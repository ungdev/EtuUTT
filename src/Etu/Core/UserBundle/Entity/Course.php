<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User course
 *
 * @ORM\Table(name="etu_users_courses")
 * @ORM\Entity
 */
class Course
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

	/**
	 * @var User $user
	 *
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn()
	 */
    private $user;

    /**
     * @var integer
     *
     * @ORM\Column(name="start", type="time")
     */
    private $start;

    /**
     * @var string
     *
     * @ORM\Column(name="end", type="time")
     */
    private $end;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="week", type="string", length=10)
	 */
	private $week;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="fullName", type="string", length=50)
	 */
	private $type;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="room", type="string", length=50)
	 */
	private $room;

	/**
	 * @param string $end
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
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $room
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
	 * @param int $start
	 * @return Course
	 */
	public function setStart($start)
	{
		$this->start = $start;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getStart()
	{
		return $this->start;
	}

	/**
	 * @param string $week
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
	 * @param string $type
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
}
