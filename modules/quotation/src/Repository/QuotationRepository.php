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

    public function __construct(
        Connection $connection,
        $databasePrefix
    ) {
        $this->connection = $connection;
        $this->databasePrefix = $databasePrefix;
    }

    public function findQuotationById()
    {
        $qb = $this->connection->createQueryBuilder($);
        $qb
            ->addSelect('q.*')
            ->addSelect('c.firstname, c.lastname')
            ->from($this->databasePrefix . 'quotation', 'q')
            ->from($this->databasePrefix . 'customer' . 'c')
            ->setParameter()
        ;

        return $qb->execute()->fetchAll();
    }
}
