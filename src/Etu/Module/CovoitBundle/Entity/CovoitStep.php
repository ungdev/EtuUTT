<?php

namespace Etu\Module\CovoitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * CovoitStep
 *
 * @ORM\Table(name="etu_covoits_steps")
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class CovoitStep
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Covoit
     *
     * @ORM\ManyToOne(targetEntity="Covoit", inversedBy="steps")
     * @ORM\JoinColumn()
     */
    private $covoit;

    /**
     * @var \Etu\Core\CoreBundle\Entity\City
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\CoreBundle\Entity\City")
     * @ORM\JoinColumn()
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    private $adress;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    private $hour;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal")
     */
    private $price;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $isFull;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint")
     */
    private $order;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $deletedAt;

    /**
     * @var CovoitSubscription[]
     *
     * @ORM\OneToMany(targetEntity="Etu\Module\CovoitBundle\Entity\CovoitSubscription", mappedBy="step")
     * @ORM\JoinColumn()
     */
    private $sbuscriptions;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set adress
     *
     * @param string $adress
     * @return CovoitStep
     */
    public function setAdress($adress)
    {
        $this->adress = $adress;
    
        return $this;
    }

    /**
     * Get adress
     *
     * @return string 
     */
    public function getAdress()
    {
        return $this->adress;
    }

    /**
     * Set hour
     *
     * @param string $hour
     * @return CovoitStep
     */
    public function setHour($hour)
    {
        $this->hour = $hour;
    
        return $this;
    }

    /**
     * Get hour
     *
     * @return string 
     */
    public function getHour()
    {
        return $this->hour;
    }

    /**
     * Set price
     *
     * @param string $price
     * @return CovoitStep
     */
    public function setPrice($price)
    {
        $this->price = $price;
    
        return $this;
    }

    /**
     * Get price
     *
     * @return string 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set isFull
     *
     * @param boolean $isFull
     * @return CovoitStep
     */
    public function setIsFull($isFull)
    {
        $this->isFull = $isFull;
    
        return $this;
    }

    /**
     * Get isFull
     *
     * @return boolean 
     */
    public function getIsFull()
    {
        return $this->isFull;
    }

    /**
     * Set order
     *
     * @param integer $order
     * @return CovoitStep
     */
    public function setOrder($order)
    {
        $this->order = $order;
    
        return $this;
    }

    /**
     * Get order
     *
     * @return integer 
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return CovoitStep
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return CovoitStep
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    
        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime 
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set covoit
     *
     * @param \Etu\Module\CovoitBundle\Entity\Covoit $covoit
     * @return CovoitStep
     */
    public function setCovoit(\Etu\Module\CovoitBundle\Entity\Covoit $covoit = null)
    {
        $this->covoit = $covoit;
    
        return $this;
    }

    /**
     * Get covoit
     *
     * @return \Etu\Module\CovoitBundle\Entity\Covoit 
     */
    public function getCovoit()
    {
        return $this->covoit;
    }

    /**
     * Set city
     *
     * @param \Etu\Core\CoreBundle\Entity\City $city
     * @return CovoitStep
     */
    public function setCity(\Etu\Core\CoreBundle\Entity\City $city = null)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return \Etu\Core\CoreBundle\Entity\City 
     */
    public function getCity()
    {
        return $this->city;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sbuscriptions = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add sbuscriptions
     *
     * @param \Etu\Module\CovoitBundle\Entity\CovoitSubscription $sbuscriptions
     * @return CovoitStep
     */
    public function addSbuscription(\Etu\Module\CovoitBundle\Entity\CovoitSubscription $sbuscriptions)
    {
        $this->sbuscriptions[] = $sbuscriptions;
    
        return $this;
    }

    /**
     * Remove sbuscriptions
     *
     * @param \Etu\Module\CovoitBundle\Entity\CovoitSubscription $sbuscriptions
     */
    public function removeSbuscription(\Etu\Module\CovoitBundle\Entity\CovoitSubscription $sbuscriptions)
    {
        $this->sbuscriptions->removeElement($sbuscriptions);
    }

    /**
     * Get sbuscriptions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSbuscriptions()
    {
        return $this->sbuscriptions;
    }
}