<?php

namespace Quotation\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Quotation\Entity\Quotation;

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

        if ($page !== null) {
            $firstResult = ($page - 1) * Quotation::NB_MAX_QUOTATIONS_PER_PAGE;
            $query->setFirstResult($firstResult)->setMaxResults((Quotation::NB_MAX_QUOTATIONS_PER_PAGE));
        }

        $query
            ->addSelect('q.*', 'c.firstname', 'c.lastname', 'cp.id_cart', 'cp.quantity', 'p.price')
            ->addSelect('SUM(p.price * cp.quantity) AS total_product_price')
            ->from($this->databasePrefix . 'quotation', 'q')
            ->addGroupBy('q.id_quotation')
            ->join('q', $this->databasePrefix . 'customer', 'c', 'q.id_customer = c.id_customer')
            ->join('q', $this->databasePrefix . 'cart_product', 'cp', 'q.id_cart = cp.id_cart')
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
            ->join('q', $this->databasePrefix . 'cart_product', 'cp', 'q.id_cart = cp.id_cart')
            ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product');
    }

    /**
     * Fonction pour filtrer les devis sur plusieurs critères
     * @param int $page
     * @param string $name
     * @param string $reference
     * @param string $status
     * @param string $start
     * @param string $end
     * @return array
     */
    public function findQuotationsByFilters(
        int $page,
        string $name = null,
        string $reference = null,
        string $status = null,
        string $start = null,
        string $end = null
    ): array {
        $query = $this->connection->createQueryBuilder();
        $query->addSelect('q.*', 'c.firstname', 'c.lastname')
            ->addSelect('SUM(p.price * cp.quantity) AS total_product_price');

        $filterSearch = [$name, $reference, $status, $start, $end];

        /**
         * Recherches composées à partir du nom du client avec la référence de la commande et les dates
         */
        switch ($filterSearch):
            case ('' !== $name && null !== $name) && ('' !== $reference && null !== $reference):
                $query->from($this->databasePrefix . 'customer', 'c')
                    ->join('c', $this->databasePrefix . 'quotation', 'q', 'q.id_customer = c.id_customer')
                    ->join('q', $this->databasePrefix . 'cart_product', 'cp', 'q.id_cart = cp.id_cart')
                    ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product')
                    ->addGroupBy('q.id_quotation')
                    ->where('(c.firstname LIKE :name OR c.lastname LIKE :name) AND q.reference LIKE :reference')
                    ->setParameters(['name' => '%' . $name . '%', 'reference' =>  '%' . $reference . '%']);
                break;
            case ('' !== $name && null !== $name) && ('' !== $start && null !== $start) && ('' !== $end && null !== $end):
                $this->addQuotationFromAndJoin($query);
                $query->where('(c.firstname LIKE :name OR c.lastname LIKE :name) AND q.date_add >= :interval_start AND q.date_add <= :interval_end')
                    ->setParameters(['name' => '%' . $name . '%', 'interval_start' => $start,
                        'interval_end' => preg_replace('/_/', '', $end)]);
                break;
            case ('' !== $name && null !== $name) && ('' !== $start && null !== $start):
                $this->addQuotationFromAndJoin($query);
                $query->where('(c.firstname LIKE :name OR c.lastname LIKE :name) AND q.date_add >= :interval_start')
                    ->setParameters(['name' => '%' . $name . '%', 'interval_start' => $start]);
                break;
            case ('' !== $name && null !== $name) && ('' !== $end&& null !== $end):
                $this->addQuotationFromAndJoin($query);
                $query->where('(c.firstname LIKE :name OR c.lastname LIKE :name) AND q.date_add <= :interval_end')
                    ->setParameters(['name' => '%' . $name . '%', 'interval_end' => preg_replace('/_/', '', $end)]);
                break;

            /**
             * Recherches composées à partir du status de la commande avec les dates
             */
            case ('' !== $status && null !== $status) && ('' !== $start && null !== $start) && ('' !== $end && null !== $end):
                $this->addQuotationFromAndJoin($query);
                $query->where('q.status = :status AND q.date_add >= :interval_start AND q.date_add <= :interval_end')
                    ->setParameters(['status' => $status, 'interval_start' => $start,
                        'interval_end' => preg_replace('/_/', '', $end)])
                    ->orderBy('q.date_add', 'DESC');
                break;
            case ('' !== $status && null !== $status) && ('' !== $start && null !== $start):
                $this->addQuotationFromAndJoin($query);
                $query->where('q.status = :status AND q.date_add >= :interval_start')
                    ->setParameters(['status' => $status, 'interval_start' => $start])
                    ->orderBy('q.date_add', 'DESC');
                break;
            case ('' !== $status && null !== $status) && ('' !== $end && null !== $end):
                $this->addQuotationFromAndJoin($query);
                $query->where('q.status = :status AND q.date_add <= :interval_end')
                    ->setParameters(['status' => $status, 'interval_end' => preg_replace('/_/', '', $end)])
                    ->orderBy('q.date_add', 'DESC');
                break;

            /**
             * Conditions pour une recherche simplifiée à partir d'un seul élément
             */
            case '' !== $name && null !== $name:
                $query->from($this->databasePrefix . 'customer', 'c')
                    ->join('c', $this->databasePrefix . 'quotation', 'q', 'q.id_customer = c.id_customer')
                    ->join('q', $this->databasePrefix . 'cart_product', 'cp', 'q.id_cart = cp.id_cart')
                    ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product')
                    ->where('c.firstname LIKE :name OR c.lastname LIKE :name')
                    ->addGroupBy('q.id_quotation')
                    ->setParameter('name', '%' . $name . '%');
                break;
            case '' !== $reference && null !== $reference:
                $this->addQuotationFromAndJoin($query);
                $query->where('q.reference LIKE :reference')
                    ->setParameter('reference', '%' . $reference . '%');
                break;
            case '' !== $status && null !== $status:
                $this->addQuotationFromAndJoin($query);
                $query->where('q.status = :status')
                    ->setParameter('status', $status);
                break;
            case ('' !== $start && null !== $start) && ('' !== $end && null !== $end):
                $this->addQuotationFromAndJoin($query);
                $query->where('q.date_add >= :interval_start AND q.date_add <= :interval_end')
                    ->setParameters(['interval_start' => $start, 'interval_end' => preg_replace('/_/', '', $end)])
                    ->orderBy('q.date_add', 'DESC');
                break;
            case '' !== $start && null !== $start:
                $this->addQuotationFromAndJoin($query);
                $query->where('q.date_add >= :interval_start')
                    ->setParameter('interval_start', $start)
                    ->orderBy('q.date_add', 'DESC');
                break;
            case '' !== $end && null !== $end:
                $this->addQuotationFromAndJoin($query);
                $query->where('q.date_add <= :interval_end')
                    ->setParameter('interval_end', preg_replace('/_/', '', $end))
                    ->orderBy('q.date_add', 'DESC');
                break;
            default:
                $this->addQuotationFromAndJoin($query);
                $query->addGroupBy('q.id_quotation');
        endswitch;

        $count = count($query->execute()->fetchAll());

        if (is_numeric($page)) {
            $firstResult = ($page - 1) * Quotation::NB_MAX_QUOTATIONS_PER_PAGE;
            $query->setFirstResult($firstResult)->setMaxResults(Quotation::NB_MAX_QUOTATIONS_PER_PAGE);
        }
        return ['nbRecords' => $count, 'records' => $query->execute()->fetchAll()];
    }

    /**
     * @param $id_quotation
     * @return mixed
     */
    public function findQuotationById($id_quotation)
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('q.*', 'c.firstname', 'c.lastname')
            ->addSelect('SUM(p.price * cp.quantity) AS total_product_price')
            ->addSelect('o.total_shipping')
            ->addSelect('o.total_shipping * 20 / 100 AS tva_shipping')
            ->addSelect('cr.reduction_amount')
            ->addSelect('cr.reduction_amount * 20 / 100 AS tva_reduction_amount')
            ->from($this->databasePrefix . 'quotation', 'q')
            ->join('q', $this->databasePrefix . 'customer', 'c', 'c.id_customer = q.id_customer')
            ->join('q', $this->databasePrefix . 'cart_product', 'cp', 'q.id_cart = cp.id_cart')
            ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product')
            ->join('c', $this->databasePrefix . 'orders', 'o', 'c.id_customer = o.id_customer')
            ->join('c', $this->databasePrefix . 'cart_rule', 'cr', 'cr.id_customer = c.id_customer')
            ->where('q.id_quotation = :id_quotation')
            ->setParameter('id_quotation', $id_quotation)
            ->execute()
            ->fetch();
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
            ->addSelect('ROUND(SUM(p.price * cp.quantity), 2) AS total_cart')
            ->addSelect('carrier.name AS carrier')
            ->addSelect('ca.id_address_delivery', 'ca.id_address_invoice')
            ->from($this->databasePrefix . 'cart', 'ca')
            ->join('ca', $this->databasePrefix . 'customer', 'c', 'ca.id_customer = c.id_customer')
            ->join('ca', $this->databasePrefix . 'cart_product', 'cp', 'ca.id_cart = cp.id_cart')
            ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product')
            ->join('ca', $this->databasePrefix . 'carrier', 'carrier', 'ca.id_carrier = carrier.id_carrier')
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

        $query = $this->connection->createQueryBuilder()
            ->addSelect('p.id_product', 'pl.name AS product_name', 'p.reference AS product_reference', 'p.price AS product_price', 'cp.quantity AS product_quantity')
            ->addSelect('p.price * cp.quantity AS total_product')
            ->addSelect('t.rate')
            ->addSelect('cp.id_product_attribute')
            ->from($this->databasePrefix . 'product', 'p')
            ->join('p', $this->databasePrefix . 'cart_product', 'cp', 'cp.id_product = p.id_product')
            ->join('cp', $this->databasePrefix . 'cart', 'ca', 'cp.id_cart = ca.id_cart')
            ->join('ca', $this->databasePrefix . 'customer', 'c', 'ca.id_customer = c.id_customer')
            ->join('p', $this->databasePrefix . 'product_lang', 'pl', 'p.id_product = pl.id_product')
            ->join('p', $this->databasePrefix . 'tax_rule', 'tr', 'p.id_tax_rules_group = tr.id_tax_rules_group')
            ->join('tr', $this->databasePrefix . 'tax', 't', 'tr.id_tax = t.id_tax')
            ->where($expr->eq('ca.id_cart', ':id_cart'))
            ->andWhere('tr.id_country = 8')
            ->setParameter('id_cart', $idCart);

        return $query->execute()->fetchAll();
    }

    /**
     * @return mixed[]
     */
    public function findOneCartById($id_cart)
    {
        $expr = $this->connection->getExpressionBuilder();

        return $this->connection->createQueryBuilder()
            ->addSelect('ca.id_cart', 'ca.date_add AS date_cart')
            ->addSelect('SUM(p.price * cp.quantity) AS total_cart')
            ->from($this->databasePrefix . 'cart', 'ca')
            ->join('ca', $this->databasePrefix . 'cart_product', 'cp', 'ca.id_cart = cp.id_cart')
            ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product')
            ->where($expr->eq('ca.id_cart', ':id_cart'))
            ->addGroupBy('ca.id_cart')
            ->setParameter('id_cart', $id_cart)->execute()->fetch();
    }

    /**
     * @return mixed[]
     */
    public function findOrderByCart($idCart)
    {
        $query = $this->connection->createQueryBuilder()
            ->addSelect('o.id_order', 'o.reference AS order_reference', 'o.id_cart', 'o.date_add AS date_order')
            ->from($this->databasePrefix . 'orders', 'o')
            ->where('o.id_cart = :id_cart')
            ->setParameter('id_cart', $idCart);
        return $query->addGroupBy('o.id_order')->execute()->fetch();
    }

    /**
     * @return mixed[]
     */
    public function findQuotationByCart($idCart)
    {
        $query = $this->connection->createQueryBuilder()
            ->addSelect('q.id_quotation', 'q.reference AS quotation_reference', 'q.id_cart', 'q.date_add AS date_quotation')
            ->from($this->databasePrefix . 'quotation', 'q')
            ->where('q.id_cart = :id_cart')
            ->setParameter('id_cart', $idCart);
        return $query->addGroupBy('q.id_quotation')->execute()->fetch();
    }

    /**
     * @return mixed[]
     */
    public function findOrdersByCustomer($idcustomer, $idCart = null)
    {
        $query = $this->connection->createQueryBuilder()

            ->addSelect('o.id_order', 'o.id_cart', 'o.reference AS order_reference', 'o.date_add AS date_order',
                'o.total_products', 'o.total_shipping', 'ROUND(o.total_paid, 2) AS total_paid', 'o.payment',
                'osl.name AS order_status')
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
            ->addSelect('q.id_customer', 'q.id_quotation', 'q.reference AS quotation_reference', 'q.date_add AS date_quotation', 'q.id_cart', 'cp.quantity', 'p.price')
            ->addSelect('SUM(p.price * cp.quantity) AS total_quotation')
            ->from($this->databasePrefix . 'quotation', 'q')
            ->addGroupBy('q.id_quotation')
            ->join('q', $this->databasePrefix . 'cart_product', 'cp', 'q.id_cart = cp.id_cart')
            ->join('cp', $this->databasePrefix . 'cart', 'ca', 'cp.id_cart = ca.id_cart')
            ->join('cp', $this->databasePrefix . 'product', 'p', 'cp.id_product = p.id_product');

        if ($idCart == null) {
            $query->where('q.id_customer = :id_customer')
                ->setParameter('id_customer', $idcustomer);
        } else {
            $query->where('q.id_customer = :id_customer AND q.id_cart = :id_cart')
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
            ->addSelect('c.id_customer', 'c.firstname', 'c.lastname', 'c.email', 'c.id_gender', 'c.birthday',
                'DATEDIFF(NOW(), c.birthday) / 365.25 AS old', 'c.date_add AS registration', 'c.id_lang',
                'c.newsletter', 'c.optin AS offer_partners', 'c.date_upd AS last_update', 'c.active')
            ->addSelect('g.id_gender', 'g.name AS title')
            ->addSelect('l.id_lang', 'l.name AS lang')
            ->addSelect('COUNT(o.id_order) AS nb_orders')
            ->from($this->databasePrefix . 'customer', 'c')
            ->join('c', $this->databasePrefix . 'gender_lang', 'g', 'c.id_gender = g.id_gender')
            ->join('c', $this->databasePrefix . 'lang', 'l', 'c.id_lang = l.id_lang')
            ->leftJoin('c', $this->databasePrefix . 'orders', 'o', 'o.id_customer = c.id_customer')
            ->where('c.id_customer = :id_customer')
            ->setParameter('id_customer', $id_customer)
            ->execute()
            ->fetch();
    }

    public function findProductsByOrder($id_order)
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('COUNT(cp.id_product) AS nb_products')
            ->from($this->databasePrefix . 'orders', 'o')
            ->join('o', $this->databasePrefix . 'cart_product', 'cp', 'o.id_cart = cp.id_cart')
            ->where('o.id_order = :id_order')
            ->setParameter('id_order', $id_order)
            ->execute()
            ->fetch();
    }

    public function findAddressesByCustomer($id_customer)
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('a.alias', 'a.id_address', 'a.company', 'a.firstname', 'a.lastname',
                        'a.address1 AS address', 'a.address2 AS further_address', 'a.postcode', 'a.city', 'cl.name AS country', 'a.phone')
            ->from($this->databasePrefix . 'address', 'a')
            ->join('a', $this->databasePrefix . 'customer', 'c', 'c.id_customer = a.id_customer')
            ->join('a', $this->databasePrefix . 'country_lang', 'cl', 'cl.id_country = a.id_country')
            ->where('c.id_customer = :id_customer')
            ->setParameter('id_customer', $id_customer)
            ->execute()
            ->fetchAll();
    }

    public function findNbCartsByCustomer($id_customer)
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('COUNT(ca.id_cart) AS nb_carts')
            ->from($this->databasePrefix . 'cart', 'ca')
            ->join('ca', $this->databasePrefix . 'customer', 'c', 'ca.id_customer = c.id_customer')
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
            ->addSelect('c.id_customer', 'c.firstname', 'c.lastname', 'c.email', 'c.birthday')
            ->from($this->databasePrefix . 'customer', 'c')
            ->where('c.firstname LIKE :query OR c.lastname LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->execute()
            ->fetchAll();
    }

    /**
     * @return mixed[]
     */
    public function findAllProducts()
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('p.id_product', "CONCAT( p.id_product, ' - ' , pl.name) AS fullname")
            ->from($this->databasePrefix . 'product', 'p')
            ->join('p', $this->databasePrefix . 'product_lang', 'pl', 'p.id_product = pl.id_product')
            ->execute()
            ->fetchAll();
    }

    /**
     * @return mixed[]
     */
    public function findProductByQuery($query)
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('p.id_product', "pl.name AS product_name")
            ->from($this->databasePrefix . 'product', 'p')
            ->join('p', $this->databasePrefix . 'product_lang', 'pl', 'p.id_product = pl.id_product')
            ->where('pl.name LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->execute()
            ->fetchAll()
            ;
    }

    /**
     * @return mixed[]
     */
    public function findOneProductById($id_product)
    {
        $expr = $this->connection->getExpressionBuilder();

        return $this->connection->createQueryBuilder()
            ->addSelect('p.id_product', 'pl.name AS product_name')
            ->addSelect("ROUND(p.price, 2) AS product_price")
            ->addSelect('pac.id_product_attribute')
            ->addSelect('sa.quantity')
            ->addSelect('p.reference AS product_reference')
            ->addSelect('t.rate')
            ->from($this->databasePrefix . 'product', 'p')
            ->join('p', $this->databasePrefix . 'product_lang', 'pl', 'p.id_product = pl.id_product')
            ->leftJoin('p', $this->databasePrefix . 'product_attribute', 'pa', 'p.id_product = pa.id_product')
            ->leftJoin('pa', $this->databasePrefix . 'product_attribute_combination', 'pac', 'pac.id_product_attribute = pa.id_product_attribute')
            ->leftJoin('pac', $this->databasePrefix . 'attribute', 'a', 'pac.id_attribute = a.id_attribute')
            ->leftJoin('pac', $this->databasePrefix . 'stock_available', 'sa', 'pac.id_product_attribute = sa.id_product_attribute')
            ->join('p', $this->databasePrefix . 'tax_rule', 'tr', 'p.id_tax_rules_group = tr.id_tax_rules_group')
            ->join('tr', $this->databasePrefix . 'tax', 't', 'tr.id_tax = t.id_tax')
            ->where($expr->eq('p.id_product', ':id_product'))
            ->addGroupBy('pac.id_product_attribute')
            ->setParameter('id_product', $id_product)->execute()->fetchAll();
    }

    /**
     * @return mixed[]
     */
    public function findAttributesByProduct(
        $id_product,
        $id_product_attribute = null
    )
    {
            $query = $this->connection->createQueryBuilder()
            ->addSelect('pac.id_product_attribute')
            ->addSelect("CONCAT( p.id_product, ' - ' , pl.name) AS product_name")
            ->addSelect("CONCAT(agl.name, ' : ' , al.name) AS attribute_details")
            ->addSelect('al.name AS attribute_details')
            ->from($this->databasePrefix . 'product', 'p')
            ->join('p', $this->databasePrefix . 'product_lang', 'pl', 'p.id_product = pl.id_product')
            ->join('p', $this->databasePrefix . 'product_attribute', 'pa', 'p.id_product = pa.id_product')
            ->join('pa', $this->databasePrefix . 'product_attribute_combination', 'pac', 'pac.id_product_attribute = pa.id_product_attribute')
            ->join('pac', $this->databasePrefix . 'attribute', 'a', 'pac.id_attribute = a.id_attribute')
            ->join('a', $this->databasePrefix . 'attribute_lang', 'al', 'al.id_attribute = a.id_attribute')
            ->join('a', $this->databasePrefix . 'attribute_group_lang', 'agl', 'agl.id_attribute_group = a.id_attribute_group');
        if ($id_product_attribute === null) {
            $query->where('p.id_product = :id_product')
                ->setParameter('id_product', $id_product);
        } else {
            $query->where('p.id_product = :id_product AND pac.id_product_attribute = :id_product_attribute')
                ->setParameters(['id_product' => $id_product, 'id_product_attribute' => $id_product_attribute]);
        }
        return $query->execute()->fetchAll();
    }

    /**
     * @return mixed[]
     */
    public function findPicturesByProduct($id_product)
    {
        $query = $this->connection->createQueryBuilder()
            ->addSelect('cp.id_product')
            ->addSelect('i.id_image')
            ->from($this->databasePrefix . 'cart_product', 'cp')
            ->join('cp', $this->databasePrefix . 'image', 'i', 'cp.id_product = i.id_product')
            ->where('cp.id_product = :id_product')
            ->setParameter('id_product', $id_product);

        return $query->execute()->fetch();
    }

    /**
     * @return mixed[]
     */
    public function findPicturesByAttributesProduct(
        $id_product,
        $id_product_attribute = null
    )
    {
        $query = $this->connection->createQueryBuilder()
            ->addSelect('cp.id_product', 'cp.id_product_attribute')
            ->addSelect('pai.id_image')
            ->from($this->databasePrefix . 'cart_product', 'cp')
            ->join('cp', $this->databasePrefix . 'product_attribute_image', 'pai', 'pai.id_product_attribute = cp.id_product_attribute');
        if ($id_product_attribute === null) {
            $query->where('cp.id_product = :id_product')
                ->setParameter('id_product', $id_product);
        } else {
            $query->where('cp.id_product = :id_product AND cp.id_product_attribute = :id_product_attribute')
                ->setParameters(['id_product' => $id_product, 'id_product_attribute' => $id_product_attribute]);
        }
        return $query->execute()->fetch();
    }

    /**
     * @return mixed[]
     */
    public function findQuantityByProduct(
        $id_product,
        $id_product_attribute = null
    )
    {
        $query = $this->connection->createQueryBuilder()
            ->addSelect('sa.quantity')
            ->from($this->databasePrefix . 'product', 'p')
            ->join('p', $this->databasePrefix . 'product_lang', 'pl', 'p.id_product = pl.id_product')
            ->leftJoin('p', $this->databasePrefix . 'product_attribute', 'pa', 'p.id_product = pa.id_product')
            ->leftJoin('pa', $this->databasePrefix . 'product_attribute_combination', 'pac', 'pac.id_product_attribute = pa.id_product_attribute')
            ->leftJoin('pac', $this->databasePrefix . 'attribute', 'a', 'pac.id_attribute = a.id_attribute')
            ->leftJoin('p', $this->databasePrefix . 'stock_available', 'sa', 'p.id_product = sa.id_product');
        if ($id_product_attribute === null) {
            $query->where('p.id_product = :id_product')
                ->setParameter('id_product', $id_product);
        } else {
            $query->where('p.id_product = :id_product AND pac.id_product_attribute = :id_product_attribute')
                ->setParameters(['id_product' => $id_product, 'id_product_attribute' => $id_product_attribute]);
        }
        return $query->execute()->fetch();
    }

    /**
     * @return mixed[]
     */
    public function getCustomerInfoById($id_customer)
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('c.id_customer', 'c.secure_key')
            ->from($this->databasePrefix . 'customer', 'c')
            ->where('c.id_customer = :id_customer')
            ->setParameter('id_customer', $id_customer)
            ->execute()->fetch();
    }

    /**
     * Add a new cart
     */
    public function addNewCart(  int $idShopGroup,
                                   int $idShop,
                                   int $idLang,
                                   int $idAdressDelivery,
                                   int $idAdressInvoice,
                                   int $idCurrency,
                                   int $id_customer,
                                   int $idGuest,
                                   string $secureKey,
                                   $dateAdd,
                                   $dateUpd,
                                   int $idCarrier = 0,
                                   string $deliveryOption = '',
                                   $recyclable = 0,
                                   $gift = 0,
                                   $mobileTheme = 0,
                                   $allowSeperatedPackage = 0)
    {
        return $this->connection->createQueryBuilder()
            ->insert($this->databasePrefix . 'cart')
            ->values([
                'id_shop_group' => ':id_shop_group',
                'id_shop' => ':id_shop',
                'id_carrier' => ':id_carrier',
                'delivery_option' => ':delivery_option',
                'id_lang' => ':id_lang',
                'id_address_delivery' => ':id_address_delivery',
                'id_address_invoice' => ':id_address_invoice',
                'id_currency' => ':id_currency',
                'id_customer' => ':id_customer',
                'id_guest' => ':id_guest',
                'secure_key' => ':secure_key',
                'recyclable' => ':recyclable',
                'gift' => ':gift',
                'mobile_theme' => ':mobile_theme',
                'allow_seperated_package' => ':allow_seperated_package',
                'date_add' => ':date_add',
                'date_upd' => ':date_upd',
            ])
            ->setParameters([
                'id_shop_group' => $idShopGroup,
                'id_shop' => $idShop,
                'id_carrier' => $idCarrier,
                'delivery_option' => $deliveryOption,
                'id_lang' => $idLang,
                'id_address_delivery' => $idAdressDelivery,
                'id_address_invoice' => $idAdressInvoice,
                'id_currency' => $idCurrency,
                'id_customer' => $id_customer,
                'id_guest' => $idGuest,
                'secure_key' => $secureKey,
                'recyclable' => $recyclable,
                'gift' => $gift,
                'mobile_theme' => $mobileTheme,
                'allow_seperated_package' => $allowSeperatedPackage,
                'date_add' => $dateAdd,
                'date_upd' => $dateUpd
            ])
            ->execute();
    }

    /**
     * @return mixed[]
     */
    public function findLastCartByCustomerId($idcustomer = null)
    {
        $expr = $this->connection->getExpressionBuilder();

        $query = $this->connection->createQueryBuilder();
        $query
            ->addSelect('ca.id_cart', 'ca.date_add AS date_cart')
            ->from($this->databasePrefix . 'cart', 'ca')
            ->orderBy('ca.date_add', 'DESC')
            ->setMaxResults(1);

        if (!is_null($idcustomer)) {
            $query->where($expr->eq('ca.id_customer', ':id_customer'))
            ->setParameter('id_customer', $idcustomer);
        }

        return $query->execute()->fetch();
    }

    /**
     * Add products into Cart
     * @param int $id_cart
     * @param int $id_product
     * @param int $idAddressDelivery
     * @param int $idShop
     * @param int $id_product_attribute
     * @param int $id_customization
     * @param $quantity
     * @param $dateAdd
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function insertProductsToCart(int $id_cart, int $id_product, int $idAddressDelivery, int $idShop, int $id_product_attribute, int $id_customization, $quantity, $dateAdd)
    {
        $query = $this->connection->createQueryBuilder()
            ->insert($this->databasePrefix . 'cart_product');

                $query->values([
                    'id_cart' => ':id_cart',
                    'id_product' => ':id_product',
                    'id_address_delivery' => ':id_address_delivery',
                    'id_shop' => ':id_shop',
                    'id_product_attribute' => ':id_product_attribute',
                    'id_customization' => ':id_customization',
                    'quantity' => ':quantity',
                    'date_add' => ':date_add',
                ])
                    ->setParameters([
                        'id_cart' => $id_cart,
                        'id_product' => $id_product,
                        'id_address_delivery' => $idAddressDelivery,
                        'id_shop' => $idShop,
                        'id_product_attribute' => $id_product_attribute,
                        'id_customization' => $id_customization,
                        'quantity' => $quantity,
                        'date_add' => $dateAdd
                    ]);
        return $query->execute();
    }

    /**
     * Update product quantity on Cart
     * @param int $id_cart
     * @param int $id_product
     * @param int $id_product_attribute
     * @param $quantity
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function updateQuantityProductOnCart(int $id_cart, int $id_product, int $id_product_attribute, $quantity)
    {
        $query = $this->connection->createQueryBuilder()
            ->update($this->databasePrefix . 'cart_product');

        $query->set('quantity', $quantity)
            ->where('id_cart = :id_cart')
            ->andWhere('id_product = :id_product')
            ->andWhere('id_product_attribute = :id_product_attribute')
            ->setParameters([
                'id_cart' => $id_cart,
                'id_product' => $id_product,
                'id_product_attribute' => $id_product_attribute,
                'quantity' => $quantity
            ]);
        return $query->execute();
    }
}