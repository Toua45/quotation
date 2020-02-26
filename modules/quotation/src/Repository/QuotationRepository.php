<?php

namespace Quotation\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

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
    public function __construct(
        Connection $connection,
        $databasePrefix
    )
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
            ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product')
        ;
        return $qb->execute()->fetchAll();
    }
    /**
     * @return mixed[]
     */
    /*public function findOrder($idCart)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('cp.id_cart', 'cp.quantity', 'p.price')
            ->addSelect('SUM(p.price * cp.quantity) AS total_product_price')
            ->from($this->databasePrefix . 'cart_product', 'cp')
            ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product')
            ->andWhere('cp.id_cart = :id_cart')
            ->setParameter('id_cart', $idCart)
        ;return $qb->execute()->fetchAll();
    }*/
}
