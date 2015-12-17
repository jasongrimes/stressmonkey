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
            ->add('time', DateTimeType::class)
            ->add('save', SubmitType::class, array('label' => 'Save'))
        ;

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

        // Make sure all the assigned tags are included in the select list
        // (even if they haven't been persisted to the database yet).
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($addTagsToForm) {
            $log = $event->getData();
            $form = $event->getForm();

            $addTagsToForm($form, $log->getManifestationTexts());
        });

        // Make sure all submitted tags are included in the select list,
        // ex. for re-rendering form on error.
        // $builder->get('manifestationTexts')->addEventListener(
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($addTagsToForm) {
                $data = $event->getData();
                var_dump($event->getData());

                $addTagsToForm($event->getForm(), $data['manifestationTexts']);
            });

    }
}