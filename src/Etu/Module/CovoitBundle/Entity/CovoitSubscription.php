<?php

namespace Etu\Module\CovoitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\User;
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
     * @var Covoit
     *
     * @ORM\ManyToOne(targetEntity="Covoit", inversedBy="subscriptions")
     * @ORM\JoinColumn()
     */
    private $covoit;

    /**
     * @var User
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
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
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
     * Set phoneNumber
     *
     * @param string $phoneNumber
     * @return CovoitSubscription
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
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
     * Set covoit
     *
     * @param Covoit $covoit
     * @return CovoitSubscription
     */
    public function setCovoit(Covoit $covoit = null)
    {
        $this->covoit = $covoit;

        return $this;
    }

    /**
     * Get covoit
     *
     * @return Covoit
     */
    public function getCovoit()
    {
        return $this->covoit;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return CovoitSubscription
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
