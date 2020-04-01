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
                'required' => false
            ])
            ->add('reference', SearchType::class, [
                'required' => false
            ])
            ->add('status', ChoiceType::class, [
                'required' => false,
                'placeholder' => 'État du devis',
//                'expanded' => true,
//                'multiple' => true,
                'choices' => [
                    'validate' => 'validate',
                    'validated' => 'validated',
                    'approved' => 'approved',
                    'refused' => 'refused'
                ]
            ])

            ->add('start', DateTimeType::class, [
                'required' => false,
                'format' => 'Y-m-d'
            ])

            ->add('end', DateTimeType::class, [
                'required' => false,
                'format' => 'Y-m-d'
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
