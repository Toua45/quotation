<?php

namespace Quotation\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Quotation\Controller\AdminQuotationController;

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
    public function findAll($page = null)
    {
        $query = $this->connection->createQueryBuilder();

        if($page !== null) {
            $firstResult = ($page -1) * AdminQuotationController::NB_MAX_QUOTATIONS_PER_PAGE;
            $query->setFirstResult($firstResult)->setMaxResults((AdminQuotationController::NB_MAX_QUOTATIONS_PER_PAGE));
        }

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
     * Sélection de la table 'quotation' reliée à la table 'customer'
     */
    private function addQuotationFromAndJoin(QueryBuilder $query)
    {
        return $query
            ->from($this->databasePrefix . 'quotation', 'q')
            ->addGroupBy('q.id_quotation')
            ->join('q', $this->databasePrefix . 'customer', 'c', 'c.id_customer = q.id_customer')
            ->join('q', $this->databasePrefix . 'cart_product', 'cp', 'q.id_cart_product = cp.id_cart')
            ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product');
    }

    /**
     * Fonction pour filtrer les devis sur plusieurs critères
     * name type=string
     * reference type=integer
     * status type=string
     * start type=datetime
     * end type=datetime
     */
    public function findQuotationsByFilters($name = null, $reference = null, $status = null, $start = null, $end = null, $page = null)
    {
        $query = $this->connection->createQueryBuilder();

        if($page !== null) {
            $firstResult = ($page-1) * AdminQuotationController::NB_MAX_QUOTATIONS_PER_PAGE;
            $query->setFirstResult($firstResult)->setMaxResults((AdminQuotationController::NB_MAX_QUOTATIONS_PER_PAGE));
        }

        $query->addSelect('q.*', 'c.firstname', 'c.lastname')
            ->addSelect('SUM(p.price * cp.quantity) AS total_product_price');


        $filterSearch = [$name, $reference, $status, $start, $end];

        /**
         * Recherches composées à partir du nom du client avec la référence de la commande et les dates
         */
        switch ($filterSearch):
            case '' !== $name && '' !== $reference:
                return $query
                    ->from($this->databasePrefix . 'customer', 'c')
                    ->join('c', $this->databasePrefix . 'quotation', 'q', 'q.id_customer = c.id_customer')
                    ->join('q', $this->databasePrefix . 'cart_product', 'cp', 'q.id_cart_product = cp.id_cart')
                    ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product')
                    ->addGroupBy('q.id_quotation')
                    ->where('(c.firstname LIKE :name OR c.lastname LIKE :name)
                        AND q.reference = :reference')
                    ->setParameters(['name' => '%' . $name . '%', 'reference' => $reference])
                    ->execute()->fetchAll();
                break;
            case '' !== $name && '' !== $start && '' !== $end:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('(c.firstname LIKE :name OR c.lastname LIKE :name)
                    AND q.date_add >= :interval_start AND q.date_add <= :interval_end')
                    ->setParameters(['name' => '%' . $name . '%', 'interval_start' => $start,
                        'interval_end' => preg_replace('/_/', '', $end)])
                    ->execute()->fetchAll();
                break;
            case '' !== $name && '' !== $start:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('(c.firstname LIKE :name OR c.lastname LIKE :name)
                    AND q.date_add >= :interval_start')
                    ->setParameters(['name' => '%' . $name . '%', 'interval_start' => $start])
                    ->execute()->fetchAll();
                break;
            case '' !== $name && '' !== $end:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('(c.firstname LIKE :name OR c.lastname LIKE :name)
                    AND q.date_add <= :interval_end')
                    ->setParameters(['name' => '%' . $name . '%', 'interval_end' => preg_replace('/_/', '', $end)])
                    ->execute()->fetchAll();
                break;

            /**
             * Recherches composées à partir du status de la commande avec les dates
             */
            case '' !== $status && '' !== $start && '' !== $end:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('q.status = :status AND q.date_add >= :interval_start AND q.date_add <= :interval_end')
                    ->setParameters(['status' => $status, 'interval_start' => $start,
                        'interval_end' => preg_replace('/_/', '', $end)])
                    ->orderBy('q.date_add', 'DESC')
                    ->execute()->fetchAll();
                break;
            case '' !== $status && '' !== $start:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('q.status = :status AND q.date_add >= :interval_start')
                    ->setParameters(['status' => $status, 'interval_start' => $start])
                    ->orderBy('q.date_add', 'DESC')
                    ->execute()->fetchAll();
                break;
            case '' !== $status && '' !== $end:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('q.status = :status AND q.date_add <= :interval_end')
                    ->setParameters(['status' => $status, 'interval_end' => preg_replace('/_/', '', $end)])
                    ->orderBy('q.date_add', 'DESC')
                    ->execute()->fetchAll();
                break;


            /**
             * Conditions pour une recherche simplifiée à partir d'un seul élément
             */
            case '' !== $name:
                return $query
                    ->from($this->databasePrefix . 'customer', 'c')
                    ->join('c', $this->databasePrefix . 'quotation', 'q', 'q.id_customer = c.id_customer')
                    ->join('q', $this->databasePrefix . 'cart_product', 'cp', 'q.id_cart_product = cp.id_cart')
                    ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product')
                    ->where('c.firstname LIKE :name OR c.lastname LIKE :name')
                    ->addGroupBy('q.id_quotation')
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
            case '' !== $start && '' !== $end:
                $this->addQuotationFromAndJoin($query);
                return $query
                    ->where('q.date_add >= :interval_start AND q.date_add <= :interval_end')
                    ->setParameters(['interval_start' => $start, 'interval_end' => preg_replace('/_/', '', $end)])
                    ->orderBy('q.date_add', 'DESC')
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
            ->setParameter('id_customer', $idcustomer)->execute()->fetchAll();
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
    public function findQuotationsByCustomer($idcustomer, $idCart = null)
    {
        $query = $this->connection->createQueryBuilder()
            ->addSelect('q.id_customer', 'q.id_quotation', 'q.reference AS quotation_reference', 'q.date_add AS date_quotation', 'q.id_cart_product', 'cp.quantity', 'p.price')
            ->addSelect('SUM(p.price * cp.quantity) AS total_quotation')
            ->from($this->databasePrefix . 'quotation', 'q')
            ->addGroupBy('q.id_quotation')
            ->join('q', $this->databasePrefix . 'cart_product', 'cp', 'q.id_cart_product = cp.id_cart')
            ->join('cp', $this->databasePrefix . 'cart', 'ca', 'cp.id_cart = ca.id_cart')
            ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product');

        if ($idCart == null) {
            $query->where('q.id_customer = :id_customer')
                ->setParameter('id_customer', $idcustomer);
        } else {
            $query->where('q.id_customer = :id_customer AND q.id_cart_product = :id_cart')
                ->setParameters(['id_customer' => $idcustomer, 'id_cart' => $idCart]);
        }

        return $query->addGroupBy('q.id_quotation')->execute()->fetchAll();
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
