<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User course.
 *
 * @ORM\Table(name="etu_users_courses")
 * @ORM\Entity(repositoryClass="Etu\Core\UserBundle\Entity\Repository\CourseRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Course
{
    public const WEEK_ALL = 'T';
    public const WEEK_A = 'A';
    public const WEEK_B = 'B';

    public const DAY_MONDAY = 'monday';
    public const DAY_TUESDAY = 'tuesday';
    public const DAY_WENESDAY = 'wednesday';
    public const DAY_THURSDAY = 'thursday';
    public const DAY_FRIDAY = 'friday';
    public const DAY_SATHURDAY = 'sathurday';
    public const DAY_SUNDAY = 'sunday';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn()
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank()
     */
    protected $day;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10)
     * @Assert\NotBlank()
     */
    protected $start;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10)
     * @Assert\NotBlank()
     */
    protected $end;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10)
     * @Assert\NotBlank()
     */
    protected $week;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     */
    protected $uv;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     */
    protected $room;

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
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $deletedAt;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->room = 'NC';
    }

    /**
     * Constructor.
     */
    public static function getTodayConstant()
    {
        $map = [
            1 => self::DAY_MONDAY,
            2 => self::DAY_TUESDAY,
            3 => self::DAY_WENESDAY,
            4 => self::DAY_THURSDAY,
            5 => self::DAY_FRIDAY,
            6 => self::DAY_SATHURDAY,
            0 => self::DAY_SUNDAY,
        ];

        return $map[(int) date('w')];
    }

    /**
     * @param string $end
     *
     * @return Course
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @return int
     */
    public function getEndAsInt()
    {
        return (int) str_replace(':', '', $this->end);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $room
     *
     * @return Course
     */
    public function setRoom($room)
    {
        $this->room = $room;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @param string $day
     *
     * @return Course
     */
    public function setDay($day)
    {
        $this->day = $day;

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
     * @param string $start
     *
     * @return Course
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @return string
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getStartAsInt()
    {
        return (int) str_replace(':', '', $this->start);
    }

    /**
     * @param string $week
     *
     * @return Course
     */
    public function setWeek($week)
    {
        $this->week = $week;

        return $this;
    }

    /**
     * @return string
     */
    public function getWeek()
    {
        return $this->week;
    }

    /**
     * @param string $uv
     *
     * @return Course
     */
    public function setUv($uv)
    {
        $this->uv = $uv;

        return $this;
    }

    /**
     * @return string
     */
    public function getUv()
    {
        return $this->uv;
    }

    /**
     * @param string $type
     *
     * @return Course
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param \Etu\Core\UserBundle\Entity\User $user
     *
     * @return Course
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \Etu\Core\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
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
     * Set deletedAt.
     *
     * @param \DateTime $deletedAt
     *
     * @return $this
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
