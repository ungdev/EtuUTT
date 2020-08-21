<?php

namespace Etu\Module\EventsBundle\Entity;

use CalendR\Event\AbstractEvent;
use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\Organization;
use Gedmo\Mapping\Annotation as Gedmo;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\Point;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="etu_events")
 * @ORM\Entity(repositoryClass="EventRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Event extends AbstractEvent
{
    public const CATEGORY_CULTURE = 'culture';
    public const CATEGORY_SPORT = 'sport';
    public const CATEGORY_FORMATION = 'formation';
    public const CATEGORY_NIGHT = 'soiree';
    public const CATEGORY_OTHER = 'autre';

    public const PRIVACY_PUBLIC = 100;
    public const PRIVACY_PRIVATE = 200;
    public const PRIVACY_ORGAS = 300;
    public const PRIVACY_MEMBERS = 400;

    public static $categories = [
        self::CATEGORY_CULTURE, self::CATEGORY_SPORT, self::CATEGORY_FORMATION,
        self::CATEGORY_NIGHT, self::CATEGORY_OTHER,
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $uid;

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
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     * @Assert\Length(min = "10", max = "50")
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20)
     */
    protected $category;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @Assert\Date()
     */
    protected $begin;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @Assert\Date()
     */
    protected $end;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $isAllDay;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $location;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Length(min = "15")
     */
    protected $description;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $privacy = self::PRIVACY_PUBLIC;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $countMembers;

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
     * Temporary variable to store uploaded file during photo update.
     *
     * @var UploadedFile
     *
     * @Assert\Image(maxSize = "4M", minWidth = 100, minHeight = 100)
     */
    public $file;

    /**
     * Constructor.
     *
     * @param $uid
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
     * Upload the picture.
     *
     * @return bool
     */
    public function upload()
    {
        if (null === $this->file) {
            if (file_exists(__DIR__.'/../../../../../web/uploads/events/'.$this->getId().'.png')) {
                return false;
            } elseif (file_exists(__DIR__.'/../../../../../web/uploads/logos/'.$this->getOrga()->getLogin().'.png')) {
                copy(__DIR__.'/../../../../../web/uploads/logos/'.$this->getOrga()->getLogin().'.png', __DIR__.'/../../../../../web/uploads/events/'.$this->getId().'.png');

                return true;
            }
            copy(__DIR__.'/../../../../../web/uploads/logos/default-logo.png', __DIR__.'/../../../../../web/uploads/events/'.$this->getId().'.png');

            return true;
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

        if (!file_exists(__DIR__.'/../../../../../web/uploads/events/')) {
            mkdir(__DIR__.'/../../../../../web/uploads/events/', 0777, true);
        }

        // Save the result
        $image->save(
            __DIR__.'/../../../../../web/uploads/events/'.$this->getId().'.png'
        );

        return true;
    }

    /**
     * @param \DateTime $begin
     *
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
     *
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
     *
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
     *
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
     *
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
     * @param bool $isAllDay
     *
     * @return Event
     */
    public function setIsAllDay($isAllDay)
    {
        $this->isAllDay = $isAllDay;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAllDay()
    {
        return $this->isAllDay;
    }

    /**
     * @param $location
     *
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
     *
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
     *
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
     *
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

    /**
     * @param int $privacy
     *
     * @return Event
     */
    public function setPrivacy($privacy)
    {
        $this->privacy = $privacy;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }
}
