<?php

namespace Quotation\Form;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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

            ->add('start', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
//                'label' => false,
//                'format' => 'd-M-y',
//                'input' => 'datetime',
//                'widget' => 'single_text',
//                'html5' => false,

            ])

            ->add('end', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
//                'label' => false,
//                'format' => 'd-M-y',
//                'input' => 'datetime',
////                'widget' => 'single_text',
////                'html5' => false,

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
