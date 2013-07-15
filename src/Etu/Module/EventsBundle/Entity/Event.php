<?php

namespace Etu\Module\EventsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use CalendR\Event\AbstractEvent;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Core\UserBundle\Entity\Organization;

use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\Point;

/**
 * @ORM\Table(name="etu_events")
 * @ORM\Entity(repositoryClass="EventRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
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
	 * @ORM\Column(name="title", type="string", length=50)
	 * @Assert\NotBlank()
	 * @Assert\Length(min = "10", max = "50")
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
	 * @Assert\Date()
	 */
	protected $begin;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="end", type="datetime")
	 * @Assert\Date()
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
	 * @Assert\NotBlank()
	 * @Assert\Length(min = "15")
	 */
	protected $description;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="countMembers", type="integer")
	 */
	protected $countMembers;

	/**
	 * @var \DateTime $created
	 *
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="createdAt", type="datetime")
	 */
	protected $createdAt;

	/**
	 * @var \DateTime $deletedAt
	 *
	 * @ORM\Column(name="deletedAt", type="datetime", nullable = true)
	 */
	protected $deletedAt;

	/**
	 * Temporary variable to store uploaded file during photo update
	 *
	 * @var UploadedFile
	 *
	 * @Assert\Image(maxSize = "4M", minWidth = 100, minHeight = 100)
	 */
	public $file;


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
	 * Upload the picture
	 *
	 * @return bool
	 */
	public function upload()
	{
		if (null === $this->file) {
			return false;
		}

		/*
		 * Upload and resize
		 */
		$imagine = new Imagine();

		// Create a transparent image
		$image = $imagine->create(new Box(200, 200), new Color('000', 100));

		// Create the logo thumbnail in a 200x200 box
		$thumbnail = $imagine->open($this->file->getPathname())
			->thumbnail(new Box(200, 200), Image::THUMBNAIL_INSET);

		// Paste point
		$pastePoint = new Point(
			(200 - $thumbnail->getSize()->getWidth()) / 2,
			(200 - $thumbnail->getSize()->getHeight()) / 2
		);

		// Paste the thumbnail in the transparent image
		$image->paste($thumbnail, $pastePoint);

		if (! file_exists(__DIR__ . '/../../../../../web/events/')) {
			mkdir(__DIR__ . '/../../../../../web/events/', 0777, true);
		}

		// Save the result
		$image->save(
			__DIR__ . '/../../../../../web/events/'.
			StringManipulationExtension::slugify($this->getTitle()).'-'.$this->getId().'.png'
		);

		return true;
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

	/**
	 * Set createdAt
	 *
	 * @param \DateTime $createdAt
	 * @return $this
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
	 * @return $this
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
}
