<?php

namespace Quotation\Form;

use PrestaShop\PrestaShop\Adapter\Entity\Cart;
use PrestaShop\PrestaShop\Adapter\Entity\Customer;
use Quotation\Entity\Quotation;
use Quotation\Repository\QuotationRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuotationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customerId', SearchType::class, [
                'label' => 'Client',
                'required' => true,
                'attr' => [
                    'class' => 'linked-select',
                    'data-target' => '#quotation_cartProductId',
                    'placeholder' => 'Sélectionnez le client',
                ]
            ])
//            ->add('cartProductId', ChoiceType::class, [
//                'label' => 'Panier',
//                'multiple' => false,
//                'expanded' => false,
//                'required' => true,
//                'placeholder' => 'Sélectionnez le panier',
//                /*'choices' => array_map(function ($m) {return $m;}, $this->choicesCarts()),
//                'attr' => [
//                    'id' => 'quotation_cartProductId',
//                ]*/
//            ])

            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'attr' => [
                    'placeholder' => 'ABDY75'
                ]
            ])
            ->add('status', TextType::class, [
                'attr' => [
                    'placeholder' => 'A valider'
                ]
            ])
            ->add('messageVisible', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'placeholder' => 'Hello world',
                    'rows' => 5,
                ]
            ])
            ;
    }

    public function choicesCustomers()
    {
        $keys = $values = [];
        foreach(Customer::getCustomers() as $key => $customer) {
            $keys[] = $customer['firstname'] . ' ' . $customer['lastname'];
            $values[] = $customer['id_customer'];
        }
        return array_combine($keys, $values);
    }

    public function choicesCarts()
    {
        $idCustomer = $this->choicesCustomers();
        $keys = $values = [];
        foreach(Cart::getCustomerCarts((int)$idCustomer) as $key => $customerCart) {
            $keys[] = $customerCart['id_cart'] . ' ' . $customerCart['date_add'];
            $values[] = $customerCart['id_cart'];
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
