<?php

namespace Smart\SonataBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
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
    private AdminInterface $admin;
    private array $importPropertiesConfig;
    private array $importOptions;
    private array $properties;
    private string $identifier;

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
        $this->initImportConfig($request);

        $importPreviewData = null;
        $importException = null;

        $form = $this->createForm(ImportType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $rawData = $form->getData()['raw_data'];
                $dataToImport = ArrayUtils::getMultiArrayFromString($rawData, $this->properties, $this->importOptions);
                if (isset($this->importOptions['pre_import_transform_callback'])) {
                    $dataToImport = array_map($this->importOptions['pre_import_transform_callback'], $dataToImport);
                }

                /** @var \Doctrine\ORM\EntityManagerInterface $em */
                $em = $this->getDoctrine()->getManager();
                $entityClass = $this->admin->getClass();
                // @phpstan-ignore-next-line
                if ($form->get('import_preview')->isClicked()) {
                    $importPreviewData = (new DiffGenerator($em))->generateDiffs(
                        $entityClass,
                        $dataToImport,
                        $this->identifier,
                        $this->importOptions['identifier_callback'] ?? null
                    );
                // @phpstan-ignore-next-line
                } elseif ($form->get('import')->isClicked()) {
                    $loaderLogs = $this->handleImport($dataToImport, $em, $entityClass);
                    $loaderLogs['nb_skipped'] = StringUtils::countRows($rawData) - $loaderLogs['nb_created'] - $loaderLogs['nb_updated'];
                    $this->addFlash('sonata_flash_success', $this->trans('import.label_success', $loaderLogs));

                    return $this->redirect($this->admin->generateUrl('list'));
                } // else 'cancel_import' the form display again with the data kept in the fields
            } catch (\Exception $e) {
                $importException = $this->handleException($e);
            }
        }

        return $this->render('@SmartSonata/import/import_form.html.twig', [
            'admin' => $this->admin,
            'form' => $form->createView(),
            'show_import_preview' => $importPreviewData !== null,
            'import_preview_data' => $importPreviewData,
            'import_exception' => $importException,
        ]);
    }

    protected function initImportConfig(Request $request): void
    {
        $this->admin = $this->adminFetcher->get($request);
        if (!$this->admin instanceof ImportableAdminInterface) {
            throw new \LogicException(sprintf("Admin with code '%s' doesn't implements ImportableAdminInterface", $this->admin->getCode()));
        }

        $this->importPropertiesConfig = $this->admin->getImportProperties();
        $this->importOptions = $this->admin->getImportOptions();
        $this->properties = array_keys($this->importPropertiesConfig);
        $this->identifier = $this->properties[0];
        if (isset($this->importOptions['identifier'])) {
            $this->identifier = $this->importOptions['identifier'];
        }
    }

    protected function handleImport(array $data, EntityManagerInterface $em, string $entityClass): array
    {
        // Denormalization
        // todo enhance denormalization and type detection error by using the not yet avaiable StringEntityExtractor from the EtlBundle
        $normalizer = new ObjectNormalizer();
        $serializer = new Serializer([$normalizer]);

        $unexpectedValueErrors = [];
        foreach ($data as $rowKey => $row) {
            foreach ($row as $key => $value) {
                if (isset($this->importPropertiesConfig[$key]['relation_class'])) {
                    // @phpstan-ignore-next-line todo enhance performance on query relation
                    $row[$key] = $em->getRepository($this->importPropertiesConfig[$key]['relation_class'])->findOneBy([
                        $this->importPropertiesConfig[$key]['relation_identifier'] => $value
                    ]);
                }
            }

            try {
                $rowEntity = $serializer->denormalize($row, $entityClass);
                if (isset($this->importOptions['entity_transform_callback'])) {
                    $rowEntity = $this->importOptions['entity_transform_callback']($rowEntity);
                }
                $data[$rowKey] = $rowEntity;
            } catch (ExceptionInterface $e) {
                $unexpectedValueErrors[$rowKey] = $e;
            }
        }
        if (count($unexpectedValueErrors) > 0) {
            throw new ImportDenormalizeException($unexpectedValueErrors);
        }

        // Init Loader Config
        $loader = new DoctrineInsertUpdateLoader($em, $this->validator);
        $accessor = PropertyAccess::createPropertyAccessor();

        // add current $entityClass to process
        $loader->addEntityToProcess(
            $entityClass,
            function ($o) use ($accessor) {
                return $accessor->getValue($o, $this->identifier);
            },
            $this->identifier,
            $this->properties
        );

        // add other relation_class to process
        $processRelations = [];
        foreach ($this->importPropertiesConfig as $propertyConfig) {
            if (isset($propertyConfig['relation_class']) && !in_array($propertyConfig['relation_class'], $processRelations)) {
                $relationIdentifier = $propertyConfig['relation_identifier'];
                $loader->addEntityToProcess(
                    $propertyConfig['relation_class'],
                    function ($o) use ($accessor, $relationIdentifier) {
                        return $accessor->getValue($o, $relationIdentifier);
                    },
                    $relationIdentifier,
                );
                $processRelations[] = $propertyConfig['relation_class'];
            }
        }

        $loader->load($data);

        return $loader->getLogs()[$entityClass];
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
