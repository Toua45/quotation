<?php

namespace Quotation\Form;

use PrestaShop\PrestaShop\Adapter\Entity\Cart;
use PrestaShop\PrestaShop\Adapter\Entity\Customer;
use Quotation\Entity\Quotation;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuotationCustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customerId', SearchType::class, [
                'label' => 'Rechercher un client',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'required' => true,
                'attr' => [
                    'class' => 'linked-select',
                    'data-target' => '#quotation_cartProductId',
                    'placeholder' => 'Sélectionnez le client',
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
