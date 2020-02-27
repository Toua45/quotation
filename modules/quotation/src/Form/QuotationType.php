<?php

namespace Quotation\Form;

use PrestaShop\PrestaShop\Adapter\Entity\Customer;
use Quotation\Entity\Quotation;
use Quotation\Repository\QuotationRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('customerId', ChoiceType::class, [
                'label' => 'Client',
                'multiple' => false,
                'expanded' => false,
                'attr' => ['placeholder' => '1'],
                'choices' => array_map(function ($n) {return $n;}, $this->choices())
            ])
            ;
    }

    public function choices()
    {
        $keys = $values = [];
        foreach(Customer::getCustomers() as $key => $customer) {
            $keys[] = $customer['firstname'] . ' ' . $customer['lastname'];
            $values[] = $customer['id_customer'];
        }
        return array_combine($keys, $values);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Quotation::class,
        ]);
    }
}
