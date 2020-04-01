<?php

namespace Quotation\Form;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class QuotationSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('name', SearchType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'input-text'
                ]
            ])

            ->add('reference', SearchType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'input-text'
                ]
            ])

            ->add('status', ChoiceType::class, [
                'required' => false,
                'label' => false,
                'placeholder' => 'Afficher tous les status',
//                'expanded' => true,
//                'multiple' => true,
                'choices' => [
                    'validate' => 'validate',
                    'validated' => 'validated',
                    'approved' => 'approved',
                    'refused' => 'refused'
                ],
                'attr' => [
                    'class' => 'input-text'
                ]
            ])

            ->add('start', DateTimeType::class, [
                'required' => false,
                'label' => false,
                'input' => 'datetime',
                'format' => 'Y-m-d',
                'attr' => [
                    'class' => 'input-date'
                ]
            ])

            ->add('end', DateTimeType::class, [
                'required' => false,
                'label' => false,
                'input' => 'datetime',
                'format' => 'Y-m-d',
                'attr' => [
                    'class' => 'input-date'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
