<?php

namespace Smart\SonataBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\File;

class ImportType extends AbstractType
{
    protected TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'field.label_import_file',
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => ["text/plain", "text/x-csv", "text/csv"],
                        'maxSize' => "8M",
                    ]),
                ]
            ])
            ->add('textarea', TextareaType::class, [
                'label' => 'field.label_import_textarea',
                'required' => false
            ])
            ->add('raw_data', HiddenType::class, [
                'label' => 'field.label_import_textarea',
                'required' => false
            ])
            ->add('import_preview', SubmitType::class, [
                'label' => 'action.import_preview',
            ])
            ->add('import', SubmitType::class, [
                'label' => 'action.import',
            ])
            ->add('cancel_import', SubmitType::class, [
                'label' => 'action.cancel',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            $fileData = $data['file'];
            $textAreaData = trim($data['textarea']);
            $rawData = trim($data['raw_data']);

            // Validation au moins une donné renseigné
            if ($fileData == null && $textAreaData == '' && $rawData == '') {
                $form->addError(new FormError(
                    $this->translator->trans('import_type.no_data_error', [], 'validators')
                ));
            }

            // Validation "Un seul des 2 champs doit être renseigné"
            if ($fileData instanceof UploadedFile && $textAreaData != '') {
                $form->addError(new FormError(
                    $this->translator->trans('import_type.not_unique_data_source_error', [], 'validators')
                ));
            }

            if ($textAreaData != '') {
                $rawData = $textAreaData;
            }
            if ($fileData instanceof UploadedFile) {
                $rawData = (string) file_get_contents($fileData->getPathname());
                // Pour enlever les caractères non voulu dû à l'encodage du csv, par exemple "ï»¿" en début de fichier
                // https://stackoverflow.com/questions/10290849/how-to-remove-multiple-utf-8-bom-sequences
                $bom = pack('H*', 'EFBBBF');
                $rawData = preg_replace("/^$bom/", '', $rawData);
            }
            $data['raw_data'] = $rawData;

            // Set du raw_data caché avec les données récup sur le textarea ou le fichier
            $event->setData($data);
        });
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => 'admin']);
    }
}
