<?php

namespace Quotation\Form;

use Quotation\Entity\Quotation;
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
            ->add('id', IntegerType::class)
            ->add('reference', TextType::class)
            ->add('messageVisible', TextType::class)
            ->add('dateAdd', TextType::class)
            ->add('status', TextType::class)
            ->add('idCart', IntegerType::class)
            ->add('idCustomer', IntegerType::class)
            ->add('idCustomerThread', IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Quotation::class,
        ]);
    }
}
