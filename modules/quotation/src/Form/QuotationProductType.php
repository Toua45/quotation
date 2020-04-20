<?php

namespace Quotation\Form;

use PrestaShop\PrestaShop\Adapter\Entity\Cart;
use PrestaShop\PrestaShop\Adapter\Entity\Customer;
use PrestaShop\PrestaShop\Adapter\Entity\Product;
use Quotation\Entity\Quotation;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ])

//            ->add('reference', ChoiceType::class, [
//                'label' => false,
//                'label_attr' => [
//                    'class' => 'form-label'
//                ],
//                'multiple' => false,
//                'expanded' => false,
//                'required' => true,
//                'choices' => array_map(function ($n) {return $n;}, $this->choicesProducts()),
//                'attr' => [
//                    'class' => 'linked-select',
//                    'data-target' => '#quotation_product_cartId',
//                ]
//            ])
            ;
    }

//    public function choicesProducts()
//    {
//        $keys = $values = [];
//        foreach(Product::getProducts(1, 0, 100, 'id_product', 'DESC') as $key => $product) {
//            $keys[] = $product['name'];
//            $values[] = $product['id_product'];
//        }
//
//        return array_combine($keys, $values);
//    }

//    public function choicesCustomers()
//    {
//        $keys = $values = [];
//        foreach(Customer::getCustomers() as $key => $customer) {
//            $keys[] = $customer['firstname'] . ' ' . $customer['lastname'];
//            $values[] = $customer['id_customer'];
//        }
//        return array_combine($keys, $values);
//    }
//
//    public function choicesCarts()
//    {
//        $idCustomer = $this->choicesCustomers();
//        $keys = $values = [];
//        foreach(Cart::getCustomerCarts((int)$idCustomer) as $key => $customerCart) {
//            $keys[] = $customerCart['id_cart'] . ' ' . $customerCart['date_add'];
//            $values[] = $customerCart['id_cart'];
//        }
//        return array_combine($keys, $values);
//    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Quotation::class,
        ]);
    }
}
