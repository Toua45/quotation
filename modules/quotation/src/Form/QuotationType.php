<?php

namespace Quotation\Form;

use Quotation\Entity\Quotation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuotationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'attr' => [
                    'placeholder' => 'ABDY75'
                ]
            ])
            ->add('messageVisible', TextType::class, [
                'label' => 'Message',
                'attr' => [
                    'placeholder' => 'Hello world'
                ]
            ])
            ->add('status', TextType::class, [
                'attr' => [
                    'placeholder' => 'A valider'
                ]
            ])
            ->add('cartProductId', IntegerType::class, [
                'label' => 'Panier',
                'attr' => [
                    'placeholder' => '2'
                ]
            ])
            ->add('customerId', IntegerType::class, [
                'label' => 'Client',
                'attr' => [
                    'placeholder' => '1'
                ]
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Quotation::class,
        ]);
    }
}
