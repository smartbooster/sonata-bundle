<?php

namespace Smart\SonataBundle\Controller;

use Smart\EtlBundle\Exception\Utils\ArrayUtils\MultiArrayHeaderConsistencyException;
use Smart\SonataBundle\Admin\ImportableAdminInterface;
use Smart\SonataBundle\Form\Type\ImportType;
use Smart\EtlBundle\Utils\ArrayUtils;
use Smart\EtlBundle\Exception\Utils\ArrayUtils\MultiArrayNbMaxRowsException;
use Smart\EtlBundle\Generator\DiffGenerator;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class ImportController extends AbstractController
{
    private AdminFetcherInterface $adminFetcher;
    private TranslatorInterface $translator;

    public function __construct(AdminFetcherInterface $adminFetcher, TranslatorInterface $translator)
    {
        $this->adminFetcher = $adminFetcher;
        $this->translator = $translator;
    }

    protected function trans(string $id, array $parameters = [], string $domain = null): string
    {
        return $this->translator->trans($id, $parameters, $domain ?? 'admin');
    }

    public function import(Request $request): Response
    {
        /** @var AdminInterface $admin */
        $admin = $this->adminFetcher->get($request);
        if (!$admin instanceof ImportableAdminInterface) {
            throw new \LogicException(sprintf("Admin with code '%s' doesn't implements ImportableAdminInterface", $admin->getCode()));
        }

        $importPreviewData = null;

        $form = $this->createForm(ImportType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            try {
                $dataToImport = ArrayUtils::getMultiArrayFromString(
                    $form->getData()['raw_data'],
                    $admin->getImportHeader(),
                    $admin->getImportOptions()
                );

                // @phpstan-ignore-next-line
                if ($form->get('import_preview')->isClicked()) {
                    $importPreviewData = (new DiffGenerator($this->getDoctrine()->getManager()))->generateDiffs(
                        $admin->getClass(),
                        $dataToImport
                    );
                // @phpstan-ignore-next-line
                } elseif ($form->get('import')->isClicked()) {
                    // @todo real import
                    $this->addFlash('sonata_flash_success', $this->trans('import.label_success', [
                        // @todo retour $importNbs
                    ]));

                    return $this->redirect($admin->generateUrl('list'));
                } // else 'cancel_import' the form display again with the data kept in the fields
            } catch (MultiArrayNbMaxRowsException $e) {
                $this->addFlash('sonata_flash_error', $this->trans($e->getMessage(), [
                    '%nbMaxRows%' => $e->nbMaxRows,
                    '%nbRows%' => $e->nbRows,
                ], 'validators'));
            } catch (MultiArrayHeaderConsistencyException $e) {
                $this->addFlash('sonata_flash_error', $this->trans($e->getMessage(), [
                    '{keys}' => implode(", ", $e->keys),
                ], 'validators'));
            } catch (\Exception $e) {
                $this->addFlash('sonata_flash_error', $this->trans("import.error") . $e->getMessage());
            }
        }

        return $this->render('@SmartSonata/import/import_form.html.twig', [
            'admin' => $admin,
            'form' => $form->createView(),
            'show_import_preview' => $importPreviewData !== null,
            'import_preview_data' => $importPreviewData,
        ]);
    }
}
