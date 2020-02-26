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
     * @ORM\Column(name="id_quotation", type="integer")
     * @ORM\GeneratedValue()
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_cart", type="integer")
     * @ORM\OneToOne(targetEntity="Cart")
     */
    private $cartId;

    /**
     * @var int
     * @ORM\Column(name="id_customer", type="integer")
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="quotations")
     */
    private $customerId;

    /**
     * @var int
     * @ORM\Column(name="id_customer_thread", type="integer", nullable=true)
     * @ORM\ManyToOne(targetEntity="CustomerThread", inversedBy="quotations")
     */
    private $customerThreadId;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=100)
     */
    private $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="messageVisible", type="text", nullable=true)
     */
    private $messageVisible;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_add", type="datetime")
     */
    private $dateAdd;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=100)
     */
    private $status;

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
     * @return int
     */
    public function getCartId()
    {
        return $this->cartId;
    }

    /**
     * @param int $cartId
     * @return Quotation
     */
    public function setCartId($cartId)
    {
        $this->cartId = $cartId;
        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param int $customerId
     * @return Quotation
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerThreadId()
    {
        return $this->customerThreadId;
    }

    /**
     * @param int $customerThreadId
     * @return Quotation
     */
    public function setCustomerThreadId($customerThreadId)
    {
        $this->customerThreadId = $customerThreadId;
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
        return $this->messageVisible;
    }

    /**
     * @param string $messageVisible
     * @return Quotation
     */
    public function setMessageVisible($messageVisible)
    {
        $this->messageVisible = $messageVisible;
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

}
