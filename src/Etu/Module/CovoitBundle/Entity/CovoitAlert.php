<?php

namespace Etu\Module\CovoitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\CoreBundle\Entity\City;
use Etu\Core\UserBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="etu_covoits_alerts")
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class CovoitAlert
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
     * @ORM\JoinColumn()
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", scale=2, precision=10, nullable=true)
     */
    private $priceMax;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\Date()
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\Date()
     */
    private $endDate;

    /**
     * @var City
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\CoreBundle\Entity\City")
     * @ORM\JoinColumn()
     * @ORM\OrderBy("name")
     */
    private $startCity;

    /**
     * @var City
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\CoreBundle\Entity\City")
     * @ORM\JoinColumn()
     * @ORM\OrderBy("name")
     */
    private $endCity;

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
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @return string
     */
    function __toString()
    {
        $parts = [];

        if ($this->getStartCity()) {
            $parts[] = 'startCity: '.$this->getStartCity()->getName();
        }

        if ($this->getEndCity()) {
            $parts[] = 'endCity: '.$this->getEndCity()->getName();
        }

        if ($this->getStartDate()) {
            $parts[] = 'startDate: '.$this->getStartDate()->format('d/m/Y');
        }

        if ($this->getEndDate()) {
            $parts[] = 'endDate: '.$this->getEndDate()->format('d/m/Y');
        }

        if ($this->getPriceMax()) {
            $parts[] = 'priceMax: '.$this->getPriceMax();
        }

        return implode(', ', $parts);
    }

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
     * Set priceMax
     *
     * @param string $priceMax
     * @return CovoitAlert
     */
    public function setPriceMax($priceMax)
    {
        $this->priceMax = $priceMax;

        return $this;
    }

    /**
     * Get priceMax
     *
     * @return string
     */
    public function getPriceMax()
    {
        return $this->priceMax;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return CovoitAlert
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return CovoitAlert
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return CovoitAlert
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return CovoitAlert
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return CovoitAlert
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
     * Set user
     *
     * @param User $user
     * @return CovoitAlert
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

    /**
     * Set startCity
     *
     * @param City $startCity
     * @return CovoitAlert
     */
    public function setStartCity(City $startCity = null)
    {
        $this->startCity = $startCity;

        return $this;
    }

    /**
     * Get startCity
     *
     * @return City
     */
    public function getStartCity()
    {
        return $this->startCity;
    }

    /**
     * Set endCity
     *
     * @param City $endCity
     * @return CovoitAlert
     */
    public function setEndCity(City $endCity = null)
    {
        $this->endCity = $endCity;

        return $this;
    }

    /**
     * Get endCity
     *
     * @return City
     */
    public function getEndCity()
    {
        return $this->endCity;
    }
}
