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
            ->fetchAll()
            ;
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
            ->fetchAll()
            ;
    }

    /**
     * @return mixed[]
     */
    public function findAllDetailsByCustomer($id_customer)
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('c.id_customer', 'ca.id_cart', 'ca.date_add AS date_cart')
            ->addSelect('p.price', 'cp.quantity')
//            ->addSelect('SUM(p.price * cp.quantity) AS total_product_price')
            ->addSelect('o.id_order', 'o.date_add AS date_order' , 'o.total_paid', 'o.payment')
            ->addSelect('q.id_quotation', 'q.date_add AS date_quotation')
            ->from($this->databasePrefix . 'customer', 'c')
            ->join('c', $this->databasePrefix . 'cart', 'ca', 'ca.id_customer = c.id_customer')
            ->join('c', $this->databasePrefix . 'orders', 'o', 'c.id_customer = o.id_customer')
            ->join('c', $this->databasePrefix . 'quotation', 'q', 'q.id_customer = c.id_customer')
            ->join('q', $this->databasePrefix . 'cart_product', 'cp', 'q.id_cart_product = cp.id_cart')
            ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product')
//            ->join('cp', $this->databasePrefix . 'cart', 'ca', 'ca.id_cart = cp.id_cart')
            ->where('c.id_customer = :id_customer')
            ->setParameter('id_customer', $id_customer)
            ->execute()
            ->fetchAll()
            ;
    }

    /**
     * @return mixed[]
     */
    public function findCartsByCustomer($idcustomer)
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('cp.id_cart', 'cp.date_add', 'c.id_customer')
            ->from($this->databasePrefix . 'cart_product', 'cp')
            ->join('cp', $this->databasePrefix . 'cart', 'c', 'c.id_cart = cp.id_cart')
            ->where('id_customer = :id_customer')
            ->setParameter('id_customer', $idcustomer)
            ->execute()
            ->fetchAll()
            ;
    }

    /**
     * @return mixed[]
     */
//    public function findOrdersByCustomer($idcustomer)
//    {
//        return $this->connection->createQueryBuilder()
//            ->addSelect('o.id_order', 'o.date_add', 'o.id_customer')
//            ->from($this->databasePrefix . 'orders', 'o')
//            ->where('id_customer = :id_customer')
//            ->setParameter('id_customer', $idcustomer)
//            ->execute()
//            ->fetchAll()
//            ;
//    }

    /**
     * @return mixed[]
     */
    public function findOneCustomerById($id_customer)
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('c.id_customer', 'c.firstname', 'c.lastname')
            ->from($this->databasePrefix . 'customer', 'c')
            ->where('c.id_customer = :id_customer')
            ->setParameter('id_customer', $id_customer)
            ->execute()
            ->fetch()
            ;
    }

    /**
     * @return mixed[]
     */
    public function findByQuery($query)
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('c.id_customer', 'c.firstname', 'c.lastname')
            ->from($this->databasePrefix . 'customer', 'c')
            ->where('c.firstname LIKE :query OR c.lastname LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->execute()
            ->fetchAll()
            ;
    }
}
