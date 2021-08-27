<?php

namespace Smart\SonataBundle\Controller;

use Smart\SonataBundle\Admin\ImportableAdminInterface;
use Smart\SonataBundle\Form\Type\ImportType;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class ImportController extends AbstractController
{
    private AdminFetcherInterface $adminFetcher;

    public function __construct(AdminFetcherInterface $adminFetcher)
    {
        $this->adminFetcher = $adminFetcher;
    }

    public function import(Request $request): Response
    {
        /** @var AdminInterface $admin */
        $admin = $this->adminFetcher->get($request);
        if (!$admin instanceof ImportableAdminInterface) {
            throw new \LogicException(sprintf("Admin with code '%s' doesn't implements ImportableAdminInterface", $admin->getCode()));
        }

        $showImportPreview = false;
        // @todo preview
        $importPreviewData = null;

        $form = $this->createForm(ImportType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            // @todo traitement
        }

        return $this->render('@SmartSonata/import/import_form.html.twig', [
            'admin' => $admin,
            'form' => $form->createView(),
            'show_import_preview' => $showImportPreview,
            'import_preview_data' => $importPreviewData,
        ]);
    }
}
