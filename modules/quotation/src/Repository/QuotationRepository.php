<?php

namespace Quotation\Repository;

use Doctrine\DBAL\Connection;

class QuotationRepository
{
    /**
     * @var Connection the Database connection
     */
    private $connection;

    /**
     * @var string the Database prefix
     */
    private $databasePrefix;

    /**
     * QuotationRepository constructor.
     * @param Connection $connection
     * @param $databasePrefix
     */
    public function __construct(Connection $connection, $databasePrefix)
    {
        $this->connection = $connection;
        $this->databasePrefix = $databasePrefix;
    }

    /**
     * @return mixed[]
     */
    public function findAll()
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('q.*', 'c.firstname', 'c.lastname', 'cp.id_cart', 'cp.quantity', 'p.price')
            ->addSelect('SUM(p.price * cp.quantity) AS total_product_price')
            ->from($this->databasePrefix . 'quotation', 'q')
            ->addGroupBy('q.id_quotation')
            ->join('q', $this->databasePrefix . 'customer', 'c', 'q.id_customer = c.id_customer')
            ->join('q', $this->databasePrefix . 'cart_product', 'cp', 'q.id_cart_product = cp.id_cart')
            ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product');
        return $qb->execute()->fetchAll();
    }

    /**
     * @return mixed[]
     */
    public function findAllCustomers()
    {
        return $this->connection->createQueryBuilder()
            ->addSelect("CONCAT(c.firstname, ' ', c.lastname) AS fullname", "c.id_customer")
            ->from($this->databasePrefix . 'customer', 'c')
            ->execute()
            ->fetchAll();
    }

    /**
     * @return mixed[]
     */
    public function findAllCarts()
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('cp.id_cart', 'cp.date_add', 'c.id_customer')
            ->from($this->databasePrefix . 'cart_product', 'cp')
            ->join('cp', $this->databasePrefix . 'cart', 'c', 'c.id_cart = cp.id_cart')
            ->execute()
            ->fetchAll();
    }

    /**
     * @return mixed[]
     */
    public function findCartsByCustomer($idcustomer)
    {
        $expr = $this->connection->getExpressionBuilder();

        return $this->connection->createQueryBuilder()
            ->addSelect('ca.id_cart', 'ca.date_add AS date_cart')
            ->addSelect('ca.id_customer', 'c.firstname', ' c.lastname')
            ->addSelect('SUM(p.price * cp.quantity) AS total_cart')
            ->from($this->databasePrefix . 'cart', 'ca')
            ->join('ca', $this->databasePrefix . 'customer', 'c', 'ca.id_customer = c.id_customer')
            ->join('ca', $this->databasePrefix . 'cart_product', 'cp', 'ca.id_cart = cp.id_cart')
            ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product')
            ->where($expr->eq('ca.id_customer', ':id_customer'))
            ->addGroupBy('ca.id_cart')
            ->setParameter('id_customer', $idcustomer)
            ->execute()->fetchAll()
            ;
    }

    /**
     * @return array
     */

    public function findProductsCustomerByCarts($idCart)
    {
        $expr = $this->connection->getExpressionBuilder();

        return $this->connection->createQueryBuilder()
            ->addSelect('p.id_product', 'pl.name AS product_name', 'p.reference AS product_reference', 'p.price AS product_price', 'cp.quantity AS product_quantity')
            ->addSelect('p.price * cp.quantity AS total_product')
            ->from($this->databasePrefix . 'product', 'p')
            ->join('p', $this->databasePrefix . 'cart_product', 'cp', 'cp.id_product = p.id_product')
            ->join('cp', $this->databasePrefix . 'cart', 'ca', 'cp.id_cart = ca.id_cart')
            ->join('ca', $this->databasePrefix . 'customer', 'c', 'ca.id_customer = c.id_customer')
            ->join('p', $this->databasePrefix . 'product_lang', 'pl', 'p.id_product = pl.id_product')
            ->where($expr->eq('ca.id_cart', ':id_cart'))
            ->setParameter('id_cart', $idCart)
            ->execute()
            ->fetchAll();
    }

    /**
     * @return mixed[]
     */
    public function findOrdersByCustomer($idcustomer, $idCart = null)
    {
        $query = $this->connection->createQueryBuilder()
            ->addSelect('o.id_order', 'o.reference AS order_reference', 'o.id_cart', 'o.date_add AS date_order',
                'o.total_products', 'o.total_shipping', 'o.total_paid', 'o.payment', 'osl.name AS order_status')
            ->addSelect('o.id_customer', 'c.firstname', ' c.lastname', 'a.address1', 'a.address2', 'a.postcode', 'a.city')
            ->from($this->databasePrefix . 'orders', 'o')
            ->join('o', $this->databasePrefix . 'customer', 'c', 'o.id_customer = c.id_customer')
            ->join('o', $this->databasePrefix . 'order_state_lang', 'osl', 'o.current_state = osl.id_order_state')
            ->join('c', $this->databasePrefix . 'address', 'a', 'c.id_customer = a.id_customer');

            if ($idCart == null) {
                $query->where('o.id_customer = :id_customer')
                    ->setParameter('id_customer', $idcustomer);
            } else {
                $query->where('o.id_customer = :id_customer AND o.id_cart = :id_cart')
                    ->setParameters(['id_customer' => $idcustomer, 'id_cart' => $idCart]);
            }
            return $query->addGroupBy('o.id_order')->execute()->fetchAll();
    }

    /**
     * @return mixed[]
     */
    public function findQuotationsByCustomer($idcustomer)
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('q.id_customer', 'q.id_quotation', 'q.date_add AS date_quotation', 'cp.quantity', 'p.price')
            ->addSelect('SUM(p.price * cp.quantity) AS total_quotation')
            ->from($this->databasePrefix . 'quotation', 'q')
            ->addGroupBy('q.id_quotation')
            ->join('q', $this->databasePrefix . 'cart_product', 'cp', 'q.id_cart_product = cp.id_cart')
            ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product')
            ->where('q.id_customer = :id_customer')
            ->setParameter('id_customer', $idcustomer)
            ->execute()
            ->fetchAll()
            ;
    }

    /**
     * @return mixed[]
     */
    public function findOneCustomerById($id_customer)
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('c.id_customer', 'c.firstname', 'c.lastname', 'c.email')
            ->from($this->databasePrefix . 'customer', 'c')
            ->where('c.id_customer = :id_customer')
            ->setParameter('id_customer', $id_customer)
            ->execute()
            ->fetch();
    }

    /**
     * @return mixed[]
     */
    public function findByQuery($query)
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('c.id_customer', 'c.firstname', 'c.lastname', 'c.email', 'c.id_gender', 'c.birthday',
                'DATEDIFF(NOW(), c.birthday) / 365.25 AS old', 'c.date_add AS registration', 'c.id_lang', 'c.newsletter',
                'c.optin AS offer_partners', 'c.date_upd AS last_update', 'c.active',
                'g.id_gender', 'g.name AS title',
                'l.id_lang', 'l.name AS lang'
            )
            ->from($this->databasePrefix . 'customer', 'c')
            ->join('c', $this->databasePrefix . 'gender_lang', 'g', 'c.id_gender = g.id_gender')
            ->join('c', $this->databasePrefix . 'lang', 'l', 'c.id_lang = l.id_lang')
            ->where('c.firstname LIKE :query OR c.lastname LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->execute()
            ->fetchAll();
    }
}
