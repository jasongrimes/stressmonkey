<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class StressLogForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('level', HiddenType::class, array('label' => 'Stress level'))
            ->add('manifestationTexts', ChoiceType::class, array(
                'choices' => array(
                    'sick' => 'sick',
                    'tired' => 'tired',
                    'tension: neck/shoulders' => 'tension: neck/shoulders',
                    'butterflies' => 'butterflies',
                ),
                'required' => false,
                'label' => 'Factors',
                'multiple' => true,
            ))
            ->add('notes', TextAreaType::class, array('required' => false))
            ->add('time', DateTimeType::class, array('date_widget' => 'single_text'))
        ;


        // Callback function for adding tags to the select list,
        // used by event handlers below.
        $addTagsToForm = function(FormInterface $form, array $texts) {
            // Get the existing field & info
            $field = $form->get('manifestationTexts');
            $type = $field->getConfig()->getType()->getInnerType();
            $options = $field->getConfig()->getOptions();
            // Update the options
            foreach ($texts as $text) {
                $options['choices'][$text] = $text;
            }
            // Replace the field.
            $form->add('manifestationTexts', get_class($type), $options);
        };

        // Called before form data is populated.
        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($addTagsToForm) {
                $log = $event->getData();
                $form = $event->getForm();
                // Make sure all the *assigned* tags are included in the select list
                // (even if they haven't been persisted to the database yet).
                $addTagsToForm($form, $log->getManifestationTexts());
            }
        );

        // Called when form is submitted, before the submit is processed.
        $builder->addEventListener(FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($addTagsToForm) {
                $data = $event->getData();
                // Make sure all *submitted* tags are included in the select list,
                // ex. for re-rendering form on error.
                if (array_key_exists('manifestationTexts', $data)) {
                    $addTagsToForm($event->getForm(), $data['manifestationTexts']);
                }
            }
        );
    }
}