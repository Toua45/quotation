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
    ) {
        $this->connection = $connection;
        $this->databasePrefix = $databasePrefix;
    }

    /**
     * @param int $customerId
     * @return array
     */
    public function findCustomerById($customerId)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->connection
            ->createQueryBuilder()
            ->select('q.id_quotation, q.id_cart, q.id_customer, q.id_customer_thread, q.reference, q.messageVisible, q.date_add, q.status')
            ->addSelect('c.lastname, c.firstname')
            ->from($this->databasePrefix. 'quotation', 'q')
            ->join('q', $this->databasePrefix. 'customer', 'c', 'q.id_customer = c.id_customer')
            ->where('id_customer = :id')
            ->setParameter('id', $customerId);
        return $qb->execute()->fetchAll();
    }
}
