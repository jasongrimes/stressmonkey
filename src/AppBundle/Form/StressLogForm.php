<?php

namespace AppBundle\Form;

use AppBundle\Util\TagManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StressLogForm extends AbstractType
{
    /** @var TagManager */
    protected $tagManager;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage, TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new \LogicException(__CLASS__ . ' cannot be used without an authenticated user!');
        }

        $tags = $this->tagManager->getTextsByUser($user);
        $tag_ops = array_combine($tags, $tags);

        $builder
            ->add('level', HiddenType::class, array('label' => 'Stress level'))
            ->add('manifestationTexts', ChoiceType::class, array(
                'choices' => $tag_ops, // array('Other factors used previously' => $tag_ops),
                'required' => false,
                'label' => 'Factors',
                'multiple' => true,
            ))
            ->add('notes', TextAreaType::class, array('required' => false))
            ->add('localtime', DateTimeType::class, array('date_widget' => 'single_text'))
            ->add('timezone', TimezoneType::class)
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