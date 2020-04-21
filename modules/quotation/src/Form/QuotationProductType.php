<?php

namespace Quotation\Form;

use Quotation\Entity\Quotation;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuotationProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('cartId', SearchType::class, [
                'label' => 'Rechercher un produit',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'required' => true,
                'attr' => [
                    'placeholder' => 'Sélectionnez un produit',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Quotation::class,
        ]);
    }
}
