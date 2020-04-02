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
        $query = $this->connection->createQueryBuilder();
        $query
            ->addSelect('q.*', 'c.firstname', 'c.lastname', 'cp.id_cart', 'cp.quantity', 'p.price')
            ->addSelect('SUM(p.price * cp.quantity) AS total_product_price')
            ->from($this->databasePrefix . 'quotation', 'q')
            ->addGroupBy('q.id_quotation')
            ->join('q', $this->databasePrefix . 'customer', 'c', 'q.id_customer = c.id_customer')
            ->join('q', $this->databasePrefix . 'cart_product', 'cp', 'q.id_cart_product = cp.id_cart')
            ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product');

        return $query->execute()->fetchAll();
    }

    /**
     * Sélection de la table 'quotation' reliée à la table 'customer'
     */
    private function addQuotationFromAndJoin(QueryBuilder $query)
    {
        return $query
            ->from($this->databasePrefix . 'quotation', 'q')
            ->join('q', $this->databasePrefix . 'customer', 'c', 'c.id_customer = q.id_customer');
    }

    /**
     * Fonction pour filtrer les devis sur plusieurs critères
     * name type=string
     * reference type=integer
     * status type=string
     * start type=datetime
     * end type=datetime
     */
    public function findQuotationsByFilters($name = null, $reference = null, $status = null, $start = null, $end = null
                                            ,$_reference = null, $_status = null, $_start = null, $_end = null
    )
    {
        $query = $this->connection->createQueryBuilder();
        $query->addSelect('q.*', 'c.firstname', 'c.lastname');

        $filterSearch = [$name, $reference, $status, $start, $end
            , $_reference, $_status, $_start, $_end
        ];

        switch ($filterSearch):

            /**
             * Filtre composé à partir du nom du client
             */
            case '' !== $name && '' !== $_reference:
                return $query
                    ->from($this->databasePrefix . 'customer', 'c')
                    ->join('c', $this->databasePrefix . 'quotation', 'q', 'q.id_customer = c.id_customer')
                    ->where('(c.firstname LIKE :name OR c.lastname LIKE :name)
                        AND q.reference = :_reference')
                    ->setParameters(['name' => '%' . $name . '%', '_reference' => $_reference])
                    ->execute()->fetchAll();
                break;
            case '' !== $name && '' !== $_start && '' !== $_end:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('(c.firstname LIKE :name OR c.lastname LIKE :name)
                    AND q.date_add >= :_interval_start AND q.date_add <= :_interval_end')
                    ->setParameters(['name' => '%' . $name . '%', '_interval_start' => $_start,
                        '_interval_end' => preg_replace('/_/', '', $_end)])
                    ->execute()->fetchAll();
                break;
            case '' !== $name && '' !== $_start:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('(c.firstname LIKE :name OR c.lastname LIKE :name)
                    AND q.date_add >= :_interval_start')
                    ->setParameters(['name' => '%' . $name . '%', '_interval_start' => $_start])
                    ->execute()->fetchAll();
                break;
            case '' !== $name && '' !== $_end:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('(c.firstname LIKE :name OR c.lastname LIKE :name)
                    AND q.date_add <= :_interval_end')
                    ->setParameters(['name' => '%' . $name . '%', '_interval_end' => preg_replace('/_/', '', $_end)])
                    ->execute()->fetchAll();
                break;


                /**
                 * Filtre composé à partir du status de la commande
                 */
            case '' !== $_status && '' !== $_start && '' !== $_end:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('q.status = :_status AND q.date_add >= :_interval_start AND q.date_add <= :_interval_end')
                    ->setParameters(['_status' => $_status, '_interval_start' => $_start,
                        '_interval_end' => preg_replace('/_/', '', $_end)])
                    ->execute()->fetchAll();
                break;
            case '' !== $_status && '' !== $_start:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('q.status = :_status AND q.date_add >= :_interval_start')
                    ->setParameters(['_status' => $_status, '_interval_start' => $_start])
                    ->execute()->fetchAll();
                break;
            case '' !== $_status && '' !== $_end:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('q.status = :_status AND q.date_add <= :_interval_end')
                    ->setParameters(['_status' => $_status, '_interval_end' => preg_replace('/_/', '', $_end)])
                    ->execute()->fetchAll();
                break;



            case '' !== $name:
                return $query
                    ->from($this->databasePrefix . 'customer', 'c')
                    ->join('c', $this->databasePrefix . 'quotation', 'q', 'q.id_customer = c.id_customer')
                    ->where('c.firstname LIKE :name OR c.lastname LIKE :name')
                    ->setParameter('name', '%' . $name . '%')
                    ->execute()->fetchAll();
                break;
            case '' !== $reference:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('q.reference = :reference')
                    ->setParameter('reference', $reference)
                    ->execute()->fetchAll();
                break;
            case '' !== $status:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('q.status = :status')
                    ->setParameter('status', $status)
                    ->execute()->fetchAll();
                break;
            case '' !== $start:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('q.date_add >= :interval_start')
                    ->setParameter('interval_start', $start)
                    ->orderBy('q.date_add', 'DESC')
                    ->execute()->fetchAll();
                break;
            case '' !== $end:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('q.date_add <= :interval_end')
                    ->setParameter('interval_end', preg_replace('/_/', '', $end))
                    ->orderBy('q.date_add', 'DESC')
                    ->execute()->fetchAll();
                break;
            case '' !== $start && '' !== $end:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('q.date_add >= :interval_start AND q.date_add <= :interval_end')
                    ->setParameters(['interval_start' => $start, 'interval_end' => preg_replace('/_/', '', $end)])
                    ->execute()->fetchAll();
                break;
            default:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->addGroupBy('q.id_quotation')
                    ->execute()->fetchAll();
        endswitch;
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
        return $this->connection->createQueryBuilder()
            ->addSelect('cp.id_cart', 'cp.date_add', 'c.id_customer')
            ->from($this->databasePrefix . 'cart_product', 'cp')
            ->join('cp', $this->databasePrefix . 'cart', 'c', 'c.id_cart = cp.id_cart')
            ->where('id_customer = :id_customer')
            ->setParameter('id_customer', $idcustomer)
            ->execute()
            ->fetchAll();
    }

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
            ->fetch();
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
            ->fetchAll();
    }
}
