<?php

namespace Quotation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

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
                'choices' => [
                    'validate' => 'validate',
                    'validated' => 'validated',
                    'approved' => 'approved',
                    'refused' => 'refused'
                ]
            ])

            ->add('start', DateType::class, [
                'required' => false
            ])
            ->add('end', DateType::class, [
                'required' => false
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
