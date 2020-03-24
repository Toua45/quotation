<?php

namespace Quotation\Repository;

use Doctrine\DBAL\Connection;
use PhpOffice\PhpSpreadsheet\Calculation\DateTime;

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

    public function findQuotationsByFilters($filter, $reference = null, $oldest = null)
    {
        $query = $this->connection->createQueryBuilder();
        $query->addSelect('q.*', 'c.firstname', 'c.lastname');

        if ($filter !== null) {
            $query
                ->from($this->databasePrefix . 'customer', 'c')
                ->join('c', $this->databasePrefix . 'quotation', 'q', 'q.id_customer = c.id_customer')
                ->where('c.firstname LIKE :filter OR c.lastname LIKE :filter')
                ->setParameter('filter', '%' . $filter . '%');
            return $query->execute()->fetchAll();

        }
        if ($reference !== null) {
            $query
                ->from($this->databasePrefix . 'quotation', 'q')
                ->join('q', $this->databasePrefix . 'customer', 'c', 'c.id_customer = q.id_customer')
                ->where('q.reference = :reference')
                ->setParameter('reference', $reference);
            return $query->execute()->fetch();

        }
        if ($oldest !== null) {
            $query
                ->from($this->databasePrefix . 'quotation', 'q')
                ->join('q', $this->databasePrefix . 'customer', 'c', 'c.id_customer = q.id_customer')
                ->where('q.date_add >= :date_from')
                ->setParameter('date_from', $oldest);
            return $query->execute()->fetchAll();

        }
//        else {
//            $query
//                ->from($this->databasePrefix . 'quotation', 'q')
//                ->join('q', $this->databasePrefix . 'customer', 'c', 'c.id_customer = q.id_customer')
//                ->addGroupBy('q.id_quotation');
//        }

        //return $query->execute()->fetchAll();
    }






    //        if (!is_null($filter) || $filter !== '') {
//            $query
//                ->from($this->databasePrefix . 'customer', 'c')
//                ->join('c', $this->databasePrefix . 'quotation', 'q', 'q.id_customer = c.id_customer')
//                ->where('c.firstname LIKE :filter OR c.lastname LIKE :filter')
//                ->setParameter('filter', '%' . $filter . '%');
//
//            if (!is_null($reference)) {
//                $query
//                ->from($this->databasePrefix . 'quotation', 'q')
//                ->join('q', $this->databasePrefix . 'customer', 'c', 'c.id_customer = q.id_customer')
//                ->where('q.reference = :reference')
//                ->setParameter('reference', $reference);
//            }
//        }
//        return $query->execute()->fetchAll();


    /**
     * @return mixed[]
     */
    public
    function findAllCustomers()
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
    public
    function findAllCarts()
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
    public
    function findCartsByCustomer($idcustomer)
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
    public
    function findOneCustomerById($id_customer)
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
    public
    function findByQuery($query)
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
