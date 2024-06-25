<?php

namespace Smart\SonataBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Smart\CoreBundle\EventListener\HistoryDoctrineListener;
use Smart\SonataBundle\Form\Type\Security\ForgotPasswordType;
use Smart\SonataBundle\Mailer\BaseMailer;
use Smart\SonataBundle\Security\Form\Type\ResetPasswordType;
use Smart\SonataBundle\Security\Form\Type\UserProfileType;
use Smart\SonataBundle\Security\SmartUserInterface;
use Smart\SonataBundle\Security\Token;
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;
use Yokai\SecurityTokenBundle\Exception\TokenConsumedException;
use Yokai\SecurityTokenBundle\Exception\TokenExpiredException;
use Yokai\SecurityTokenBundle\Exception\TokenNotFoundException;
use Yokai\SecurityTokenBundle\Manager\TokenManagerInterface;

/**
 * @author Nicolas Bastien <nicolas.bastien@smartbooster.io>
 *
 * Fix phpstan error cf. https://github.com/phpstan/phpstan/issues/3200
 * @property ContainerInterface $container
 */
class AbstractSecurityController extends AbstractController
{
    /**
     * Define application context, override this in your controller
     * @var string
     */
    protected $context;
    protected TokenManagerInterface $tokenManager;
    protected BaseMailer $mailer;

    protected TranslatorInterface $translator;
    protected UserProviderInterface $userProvider;
    protected UserPasswordHasherInterface $hasher;
    protected TemplateRegistry $templateRegistry;
    protected EntityManagerInterface $entityManager;

    public function __construct(
        TokenManagerInterface $tokenManager,
        BaseMailer $mailer,
        TranslatorInterface $translator,
        UserPasswordHasherInterface $hasher,
        TemplateRegistry $templateRegistry,
        EntityManagerInterface $entityManager
    ) {
        $this->tokenManager = $tokenManager;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->hasher = $hasher;
        $this->templateRegistry = $templateRegistry;
        $this->entityManager = $entityManager;
    }

    /**
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        return $this->render($this->context . '/security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
            'layout_template' => $this->context . '/empty_layout.html.twig',
            'security_login_check_url' => $this->generateUrl($this->context . '_security_login_check'),
            'security_forgot_password_url' => $this->generateUrl($this->context . '_security_forgot_password'),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function forgotPassword(Request $request, ParameterBagInterface $parameterBag)
    {
        $form =  $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render(
                $this->context . '/security/forgot_password.html.twig',
                [
                    'form' => $form->createView(),
                    'security_login_form_url' => $this->generateUrl($this->context . '_security_login_form'),
                    'security_forgot_password_url' => $this->generateUrl($this->context . '_security_forgot_password'),
                ]
            );
        }

        try {
            $user = $this->getUserProvider()->loadUserByIdentifier($form->get('email')->getData());

            if ($user instanceof SmartUserInterface) {
                $token = $this->tokenManager->create(Token::RESET_PASSWORD, $user);

                $email = $this->mailer->newEmail($this->context . '.security.forgot_password', [
                    'domain' => $parameterBag->get('domain'),
                    'context' => $this->context,
                    'security_reset_password_route' => $this->context . '_security_reset_password',
                    'token' => $token->getValue(),
                ]);
                $this->mailer->send($email, $user->getEmail());

                $this->addFlash('success', 'flash.forgot_password.success');
            }
        } catch (UserNotFoundException $e) {
            $this->addFlash('error', 'flash.forgot_password.unknown');

            return $this->redirectToRoute($this->context . '_security_forgot_password');
        }

        return $this->redirectToRoute($this->context . '_security_login_form');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function resetPassword(Request $request, HistoryDoctrineListener $historyListener)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute($this->context . '_dashboard');
        }

        if (!$request->query->has('token')) {
            $this->addFlash('error', 'flash.security.invalid_token');

            return $this->redirectToRoute($this->context . '_security_login_form');
        }

        try {
            $token = $this->tokenManager->get(Token::RESET_PASSWORD, $request->query->get('token'));
        } catch (TokenNotFoundException $e) {
            $this->addFlash('error', 'flash.security.token_not_found');
            return $this->redirectToRoute($this->context . '_security_login_form');
        } catch (TokenExpiredException $e) {
            $this->addFlash('error', 'flash.security.token_expired');
            return $this->redirectToRoute($this->context . '_security_login_form');
        } catch (TokenConsumedException $e) {
            $this->addFlash('error', 'flash.security.token_used');
            return $this->redirectToRoute($this->context . '_security_login_form');
        }

        /** @var SmartUserInterface $user */
        $user = $this->tokenManager->getUser($token);

        $form =  $this->createForm(ResetPasswordType::class, $user);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render(
                $this->context . '/security/reset_password.html.twig',
                [
                    'token' => $token->getValue(),
                    'form' => $form->createView(),
                    'security_reset_password_route' => $this->context . '_security_reset_password'
                ]
            );
        }

        try {
            if (null !== $user->getPlainPassword()) {
                $historyListener->disable();
                $this->updateUser($user);
                $this->tokenManager->consume($token);
            }
            $this->addFlash('success', 'flash.reset_password.success');
        } catch (\Exception $e) {
            $this->addFlash('error', 'flash.reset_password.error');
        }

        return $this->redirectToRoute($this->context . '_security_login_form');
    }

    /**
     * Only use this action for sonata admin context
     * For other context override this action
     * @param Request $request
     *
     * @return Response
     */
    public function profile(Request $request)
    {
        /** @var SmartUserInterface $user */
        $user = $this->getUser();

        $form = $this->createForm(UserProfileType::class, $user, []);

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render($this->context . '/security/profile.html.twig', [
                'base_template' => $this->templateRegistry->getTemplate('layout'),
                'form'          => $form->createView(),
                'security_profile_url' => $this->generateUrl('admin_security_profile'),
            ]);
        }

        $this->updateUser($user);

        $this->addFlash('success', $this->translate('profile_edit.processed', [], 'security'));

        return $this->redirectToRoute('sonata_admin_dashboard');
    }

    /**
     * @param string      $id         The message id (may also be an object that can be cast to string)
     * @param array<array>       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     *
     * @return string
     */
    protected function translate($id, array $parameters = array(), $domain = null)
    {
        return $this->translator->trans($id, $parameters, $domain);
    }

    /**
     * @param SmartUserInterface $user
     *
     * @return void
     */
    protected function updateUser(SmartUserInterface $user)
    {
        if (null !== $user->getPlainPassword()) {
            $user->setPassword(
                $this->hasher->hashPassword($user, $user->getPlainPassword())
            );
        }

        $manager = $this->entityManager;
        $manager->persist($user);
        $manager->flush();
    }

    /**
     * Override this method if your application use custom domain
     *
     * @return string
     */
    protected function getDomain()
    {
        $toReturn = $this->container->getParameter('domain');

        if (!is_string($toReturn)) {
            throw new \LogicException('domain must be string');
        }

        return $toReturn;
    }

    public function setUserProvider(UserProviderInterface $userProvider): void
    {
        $this->userProvider = $userProvider;
    }

    protected function getUserProvider(): UserProviderInterface
    {
        return $this->userProvider;
    }
}
