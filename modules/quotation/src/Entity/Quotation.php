<?php

namespace Quotation\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */

class Quotation
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     *@ORM\Column(name="reference" type="string" length=100)
     */
    private $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="message_visible" type="text")
     */
    private $message_visible;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_add", type="datetime", nullable=false)
     */
    private $dateAdd;

    /**
     * @var string
     *
     * @ORM\Column(name="status" type="string" length=100)
     */
    private $status;


    /**
     * @var int
     *
     * @ORM\Column(name="id_cart" type="int")
     * @ORM\OneToOne(targetEntity="Cart")
     */
    private $idCart;

    /**
     * @var int
     * @ORM\Column(name="id_customer" type="int")
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="quotations")
     */
    private $idCustomer;

    /**
     * @var int
     * @ORM\Column(name="id_customer_thread" type="int")
     * @ORM\ManyToOne(targetEntity="CustomerThread", inversedBy="quotations")
     */
    private $idCustomerThread;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Quotation
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     * @return Quotation
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageVisible()
    {
        return $this->message_visible;
    }

    /**
     * @param string $message_visible
     * @return Quotation
     */
    public function setMessageVisible($message_visible)
    {
        $this->message_visible = $message_visible;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateAdd()
    {
        return $this->dateAdd;
    }

    /**
     * @param \DateTime $dateAdd
     * @return Quotation
     */
    public function setDateAdd($dateAdd)
    {
        $this->dateAdd = $dateAdd;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Quotation
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getIdCart()
    {
        return $this->idCart;
    }

    /**
     * @param int $idCart
     * @return Quotation
     */
    public function setIdCart($idCart)
    {
        $this->idCart = $idCart;
        return $this;
    }

    /**
     * @return int
     */
    public function getIdCustomer()
    {
        return $this->idCustomer;
    }

    /**
     * @param int $idCustomer
     * @return Quotation
     */
    public function setIdCustomer($idCustomer)
    {
        $this->idCustomer = $idCustomer;
        return $this;
    }

    /**
     * @return int
     */
    public function getIdCustomerThread()
    {
        return $this->idCustomerThread;
    }

    /**
     * @param int $idCustomerThread
     * @return Quotation
     */
    public function setIdCustomerThread($idCustomerThread)
    {
        $this->idCustomerThread = $idCustomerThread;
        return $this;
    }

}
