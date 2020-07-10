<?php

namespace Quotation\Form;

use Symfony\Component\Form\AbstractType;
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
                    'À valider' => 'validate',
                    'Validé' => 'validated',
                    'Refusé' => 'refused'
                ],
                'attr' => [
                    'class' => 'input-text'
                ]
            ])

            ->add('start', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => false
            ])

            ->add('end', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => false
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