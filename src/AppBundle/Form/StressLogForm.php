<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class StressLogForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('level', HiddenType::class, array('label' => 'Stress level'))
            ->add('manifestationString', TextType::class, array('required' => false, 'label' => 'Factors'))
            ->add('notes', TextAreaType::class, array('required' => false))
            ->add('time', DateTimeType::class)
            ->add('save', SubmitType::class, array('label' => 'Save'))
        ;
    }
}