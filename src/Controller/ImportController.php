<?php

namespace Smart\SonataBundle\Controller;

use Smart\EtlBundle\Exception\Utils\ArrayUtils\MultiArrayHeaderConsistencyException;
use Smart\EtlBundle\Loader\DoctrineInsertUpdateLoader;
use Smart\EtlBundle\Utils\StringUtils;
use Smart\SonataBundle\Admin\ImportableAdminInterface;
use Smart\SonataBundle\Exception\ImportDenormalizeException;
use Smart\SonataBundle\Exception\ImportNormalizeException;
use Smart\SonataBundle\Form\Type\ImportType;
use Smart\EtlBundle\Utils\ArrayUtils;
use Smart\EtlBundle\Exception\Loader\LoadUnvalidObjectsException;
use Smart\EtlBundle\Exception\Utils\ArrayUtils\MultiArrayNbMaxRowsException;
use Smart\EtlBundle\Generator\DiffGenerator;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class ImportController extends AbstractController
{
    private AdminFetcherInterface $adminFetcher;
    private TranslatorInterface $translator;
    private ValidatorInterface $validator;

    public function __construct(AdminFetcherInterface $adminFetcher, TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $this->adminFetcher = $adminFetcher;
        $this->translator = $translator;
        $this->validator = $validator;
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
        $importException = null;

        $form = $this->createForm(ImportType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $rawData = $form->getData()['raw_data'];
                $importPropertiesConfig = $admin->getImportProperties();
                $properties = array_keys($importPropertiesConfig);
                $dataToImport = ArrayUtils::getMultiArrayFromString(
                    $rawData,
                    $properties,
                    $admin->getImportOptions()
                );

                /** @var \Doctrine\ORM\EntityManagerInterface $em */
                $em = $this->getDoctrine()->getManager();
                $entityClass = $admin->getClass();
                // @phpstan-ignore-next-line
                if ($form->get('import_preview')->isClicked()) {
                    $importPreviewData = (new DiffGenerator($em))->generateDiffs(
                        $entityClass,
                        $dataToImport
                    );
                // @phpstan-ignore-next-line
                } elseif ($form->get('import')->isClicked()) {
                    $normalizer = new ObjectNormalizer();
                    $serializer = new Serializer([$normalizer]);

                    $unexpectedValueErrors = [];
                    foreach ($dataToImport as $rowKey => $row) {
                        foreach ($row as $key => $value) {
                            if (isset($importPropertiesConfig[$key]['relationClass'])) {
                                // @phpstan-ignore-next-line todo améliorer les perfs sur la récupération des relations
                                $row[$key] = $em->getRepository($importPropertiesConfig[$key]['relationClass'])->findOneBy([
                                    $importPropertiesConfig[$key]['relationIdentifier'] => $value
                                ]);
                            }
                        }

                        try {
                            // todo améliorer la détection de l'erreur des typage en passant l'extract dans l'etl + en utilisant le validator
                            $dataToImport[$rowKey] = $serializer->denormalize($row, $entityClass);
                        } catch (ExceptionInterface $e) {
                            $unexpectedValueErrors[$rowKey] = $e;
                        }
                    }
                    if (count($unexpectedValueErrors) > 0) {
                        throw new ImportDenormalizeException($unexpectedValueErrors);
                    }

                    $loader = new DoctrineInsertUpdateLoader($em, $this->validator);
                    $identifier = $properties[0];
                    $accessor = PropertyAccess::createPropertyAccessor();

                    // add current $entityClass to process
                    $loader->addEntityToProcess(
                        $entityClass,
                        function ($o) use ($identifier, $accessor) {
                            return $accessor->getValue($o, $identifier);
                        },
                        $identifier,
                        $properties
                    );

                    // add other relationClass to process
                    $processRelations = [];
                    foreach ($importPropertiesConfig as $config) {
                        if (isset($config['relationClass']) && !in_array($config['relationClass'], $processRelations)) {
                            $relationIdentifier = $config['relationIdentifier'];
                            $loader->addEntityToProcess(
                                $config['relationClass'],
                                function ($o) use ($relationIdentifier, $accessor) {
                                    return $accessor->getValue($o, $relationIdentifier);
                                },
                                $relationIdentifier,
                            );
                            $processRelations[] = $config['relationClass'];
                        }
                    }

                    $loader->load($dataToImport);

                    $loaderLogs = $loader->getLogs()[$entityClass];
                    $loaderLogs['nb_skipped'] = StringUtils::countRows($rawData) - $loaderLogs['nb_created'] - $loaderLogs['nb_updated'];
                    $this->addFlash('sonata_flash_success', $this->trans('import.label_success', $loaderLogs));

                    return $this->redirect($admin->generateUrl('list'));
                } // else 'cancel_import' the form display again with the data kept in the fields
            } catch (\Exception $e) {
                $importException = $this->handleException($e);
            }
        }

        return $this->render('@SmartSonata/import/import_form.html.twig', [
            'admin' => $admin,
            'form' => $form->createView(),
            'show_import_preview' => $importPreviewData !== null,
            'import_preview_data' => $importPreviewData,
            'import_exception' => $importException,
        ]);
    }

    protected function handleException(\Exception $e): ?\Exception
    {
        switch (get_class($e)) {
            case MultiArrayNbMaxRowsException::class:
                $message = $this->trans($e->getMessage(), [
                    '%nbMaxRows%' => $e->nbMaxRows,
                    '%nbRows%' => $e->nbRows,
                ], 'validators');
                break;
            case MultiArrayHeaderConsistencyException::class:
                $message = $this->trans($e->getMessage(), [
                    '{keys}' => implode(", ", $e->keys),
                ], 'validators');
                break;
            case LoadUnvalidObjectsException::class:
            case ImportDenormalizeException::class:
                $this->addFlash('sonata_flash_error', $this->trans($e->getMessage(), [], 'validators'));
                return $e;
            default:
                $message = $this->trans("import.error") . $e->getMessage();
                break;
        }

        $this->addFlash('sonata_flash_error', $message);

        return null;
    }
}
