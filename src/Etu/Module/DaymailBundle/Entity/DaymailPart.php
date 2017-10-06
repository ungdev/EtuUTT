<?php

namespace Etu\Module\DaymailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\Organization;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Daymail part.
 *
 * @ORM\Table(name="etu_daymail_parts")
 * @ORM\Entity()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class DaymailPart
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\Organization")
     * @ORM\JoinColumn()
     */
    protected $orga;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=60)
     * @Assert\NotBlank()
     * @Assert\Length(min = "10", max = "60")
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Length(min = "15")
     */
    protected $body;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     */
    protected $date;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10)
     */
    protected $day;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $deletedAt;

    /**
     * @param Organization $orga
     * @param \DateTime    $date
     */
    public function __construct(Organization $orga, \DateTime $date)
    {
        $this->orga = $orga;
        $this->setDate($date);
    }

    /**
     * Create the available list of days which can be used as publish day.
     *
     * @return \DateTime[]
     */
    public static function createFutureAvailableDays()
    {
        $available = [];

        for ($i = 1; $i <= 7; ++$i) {
            $day = new \DateTime();
            $day->add(new \DateInterval('P'.$i.'D'));

            $available[$day->format('d-m-Y')] = $day;
            $available[$day->format('d-m-Y')]->old = false;
        }

        // Maximum time for edition of daymail
        $current_time = new \DateTime('now');
        $max_edit_time = new \DateTime('today 06:00');
        if ($current_time < $max_edit_time) {
            $available[$current_time->format('d-m-Y')] = $current_time;
            $available[$current_time->format('d-m-Y')]->old = false;
        }

        return $available;
    }

    /**
     * @param string $body
     *
     * @return DaymailPart
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param \DateTime $date
     *
     * @return DaymailPart
     */
    public function setDate($date)
    {
        $this->date = $date;
        $this->day = $date->format('d-m-Y');

        return $this;
    }

    /**
     * @return string
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param int $id
     *
     * @return DaymailPart
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Etu\Core\UserBundle\Entity\Organization $orga
     *
     * @return DaymailPart
     */
    public function setOrga($orga)
    {
        $this->orga = $orga;

        return $this;
    }

    /**
     * @return \Etu\Core\UserBundle\Entity\Organization
     */
    public function getOrga()
    {
        return $this->orga;
    }

    /**
     * @param string $title
     *
     * @return DaymailPart
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return DaymailPart
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set deletedAt.
     *
     * @param \DateTime $deletedAt
     *
     * @return DaymailPart
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt.
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }
}
