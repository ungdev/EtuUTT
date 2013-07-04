<?php

namespace Etu\Module\EventsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CalendR\Event\AbstractEvent;

use Etu\Core\UserBundle\Entity\Organization;

/**
 * @ORM\Table(name="etu_events")
 * @ORM\Entity(repositoryClass="EventRepository")
 */
class Event extends AbstractEvent
{
	const CATEGORY_CULTURE = 'culture';
	const CATEGORY_SPORT = 'sport';
	const CATEGORY_FORMATION = 'formation';
	const CATEGORY_NIGHT = 'soiree';
	const CATEGORY_OTHER = 'autre';

	public static $categories = array(
		self::CATEGORY_CULTURE, self::CATEGORY_SPORT, self::CATEGORY_FORMATION,
		self::CATEGORY_NIGHT, self::CATEGORY_OTHER
	);

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $uid;

	/**
	 * @var Organization $orga
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\Organization")
	 * @ORM\JoinColumn()
	 */
	protected $orga;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="title", type="string", length=100)
	 */
	protected $title;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="category", type="string", length=20)
	 */
	protected $category;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="begin", type="datetime")
	 */
	protected $begin;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="end", type="datetime")
	 */
	protected $end;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="isAllDay", type="boolean")
	 */
	protected $isAllDay;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="location", type="string", length=100)
	 */
	protected $location;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="description", type="text")
	 */
	protected $description;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="countMembers", type="integer")
	 */
	protected $countMembers;


	/**
	 * Constructor
	 *
	 * @param $uid
	 * @param \DateTime $start
	 * @param \DateTime $end
	 */
	public function __construct($uid, \DateTime $start, \DateTime $end)
	{
		$this->uid = $uid;
		$this->begin = clone $start;
		$this->end = clone $end;
		$this->countMembers = 0;
		$this->isAllDay = false;
	}

	/**
	 * @param \DateTime $begin
	 * @return Event
	 */
	public function setBegin($begin)
	{
		$this->begin = $begin;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getBegin()
	{
		return $this->begin;
	}

	/**
	 * @param string $category
	 * @return Event
	 */
	public function setCategory($category)
	{
		$this->category = $category;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCategory()
	{
		return $this->category;
	}

	/**
	 * @param int $countMembers
	 * @return Event
	 */
	public function setCountMembers($countMembers)
	{
		$this->countMembers = $countMembers;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCountMembers()
	{
		return $this->countMembers;
	}

	/**
	 * @param string $description
	 * @return Event
	 */
	public function setDescription($description)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param \DateTime $end
	 * @return Event
	 */
	public function setEnd($end)
	{
		$this->end = $end;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getEnd()
	{
		return $this->end;
	}

	/**
	 * @param boolean $isAllDay
	 * @return Event
	 */
	public function setIsAllDay($isAllDay)
	{
		$this->isAllDay = $isAllDay;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getIsAllDay()
	{
		return $this->isAllDay;
	}

	/**
	 * @param $location
	 * @return $this
	 */
	public function setLocation($location)
	{
		$this->location = $location;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLocation()
	{
		return $this->location;
	}

	/**
	 * @param string $latitude
	 */
	public function setLatitude($latitude)
	{
		$this->latitude = $latitude;
	}

	/**
	 * @return string
	 */
	public function getLatitude()
	{
		return $this->latitude;
	}

	/**
	 * @param string $longitude
	 */
	public function setLongitude($longitude)
	{
		$this->longitude = $longitude;
	}

	/**
	 * @return string
	 */
	public function getLongitude()
	{
		return $this->longitude;
	}

	/**
	 * @param \Etu\Core\UserBundle\Entity\Organization $orga
	 * @return Event
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
	 * @return Event
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
	 * @param int $uid
	 * @return Event
	 */
	public function setId($uid)
	{
		$this->uid = $uid;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->uid;
	}

	/**
	 * @return int
	 */
	public function getUid()
	{
		return $this->uid;
	}
}
