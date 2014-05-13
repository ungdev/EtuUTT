<?php

namespace Etu\Module\CovoitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * CovoitSubscription
 *
 * @ORM\Table(name="etu_covoits_subscriptions")
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class CovoitSubscription
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
     * @var CovoitStep
     *
     * @ORM\ManyToOne(targetEntity="CovoitStep", inversedBy="sbuscriptions")
     * @ORM\JoinColumn()
     */
    private $step;

    /**
     * @var \Etu\Core\UserBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
     * @ORM\JoinColumn()
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    private $phoneNumber;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $asDriver;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return CovoitSubscription
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
     * @return CovoitSubscription
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
     * Set step
     *
     * @param \Etu\Module\CovoitBundle\Entity\CovoitStep $step
     * @return CovoitSubscription
     */
    public function setStep(\Etu\Module\CovoitBundle\Entity\CovoitStep $step = null)
    {
        $this->step = $step;
    
        return $this;
    }

    /**
     * Get step
     *
     * @return \Etu\Module\CovoitBundle\Entity\CovoitStep 
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set user
     *
     * @param \Etu\Core\UserBundle\Entity\User $user
     * @return CovoitSubscription
     */
    public function setUser(\Etu\Core\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Etu\Core\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param boolean $asDriver
     */
    public function setAsDriver($asDriver)
    {
        $this->asDriver = $asDriver;
    }

    /**
     * @return boolean
     */
    public function getAsDriver()
    {
        return $this->asDriver;
    }
}