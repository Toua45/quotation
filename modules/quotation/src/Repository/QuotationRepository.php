<?php

namespace Quotation\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class QuotationRepository extends EntityRepository implements ServiceEntityRepositoryInterface
{
    /**
     * @var string the Database prefix
     */
    private $databasePrefix;
    private $em;
    private $className;
    private $entity;

    /**
     * QuotationRepository constructor.
     * @param EntityManagerInterface $em
     * @param Connection $connection
     */
    public function __construct(EntityManagerInterface $em)
    {
        // Replace 'Quotation'
        $this->className = preg_replace('/(Quotation\\\Repository\\\|Repository)/', '', get_class($this));
        $this->entity = 'Quotation\\Entity\\' . $this->className;
        $this->databasePrefix;
        $this->em = $em;

        parent::__construct($em, $em->getClassMetadata($this->entity));
    }

    /**
     * @param string $className
     * @param ManagerRegistry $mr
     * @return mixed
     */
    public static function getRepository($className, EntityManagerInterface $em)
    {
        $className = 'Quotation\\Repository\\' . ucfirst($className) .'Repository';
        return new $className($em);
    }

    /**
     * @return mixed[]
     */
    public function findAllCustomerQuotations()
    {
        return $this->createQueryBuilder('q')
            //->addSelect('c.firstname', 'c.lastname')
            //->join($this->databasePrefix . 'customer', 'c', 'q.id_customer = c.id_customer')
            ->getQuery()->getResult();
    }
}
