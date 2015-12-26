<?php

namespace AppBundle\Form;

use AppBundle\Util\TagManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
use Symfony\Component\Validator\Constraints as Assert;

class FilterStressLogsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('from', DateTimeType::class, array(
                'date_widget' => 'single_text',
                'required' => false,
            ))
            ->add('to', DateTimeType::class, array(
                'date_widget' => 'single_text',
                'required' => false,
            ))
            ->add('levelOp', ChoiceType::class, array(
                'choices' => array('=' => '=', '<=' => '<=', '>=' => '>='),
            ))
            ->add('level', IntegerType::class, array(
                'constraints' => new Assert\Range(array('min' => 0, 'max' => 10)),
                'attr' => array('min' => 0, 'max' => 10),
                'required' => false,
            ))
            ->add('factorOp', ChoiceType::class, array(
                'choices' => array('All of' => 'and', 'Any of' => 'or'),
            ))
            ->add('factors', TextType::class, array(
                'required' => false,
            ))
            ->add('withNotes', CheckboxType::class, array(
                'label' => 'Only entries with notes',
                'required' => false,
            ))
            ;
    }
}
