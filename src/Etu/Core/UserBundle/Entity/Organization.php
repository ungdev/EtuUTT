<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\Point;

/**
 * Organization
 *
 * @ORM\Table(name="etu_organizations")
 * @ORM\Entity()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Organization implements UserInterface, \Serializable
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=50)
	 * @Assert\NotBlank()
	 */
	protected $login;

	/**
	 * @var User $president
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
	 * @ORM\JoinColumn()
	 */
	protected $president;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=50)
	 * @Assert\NotBlank()
	 * @Assert\Length(min = "2", max = "50")
	 */
	protected $name;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=100, nullable=true)
	 * @Assert\Email()
	 */
	protected $contactMail;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=100, nullable=true)
	 * @Assert\Regex("/^0[1-9]([-. ]?[0-9]{2}){4}$/")
	 */
	protected $contactPhone;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 * @Assert\Length(min = "15")
	 */
	protected $description;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=200, nullable=true)
	 * @Assert\Length(min = "2", max = "200")
	 */
	protected $descriptionShort;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=100, nullable=true)
	 * @Assert\Url()
	 */
	protected $website;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $logo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $countMembers;

	/**
	 * @var \DateTime $created
	 *
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(type="datetime")
	 */
	protected $createdAt;

	/**
	 * @var \DateTime $updated
	 *
	 * @Gedmo\Timestampable(on="update")
	 * @ORM\Column(type="datetime")
	 */
	protected $updatedAt;

	/**
	 * @var \DateTime $deletedAt
	 *
	 * @ORM\Column(type="datetime", nullable = true)
	 */
	protected $deletedAt;

    /**
     * @var Member[] $memberships
     *
     * @ORM\OneToMany(targetEntity="\Etu\Core\UserBundle\Entity\Member", mappedBy="organization")
     * @ORM\JoinColumn()
     */
    protected $memberships;

	/**
	 * Temporary variable to store uploaded file during photo update
	 *
	 * @var UploadedFile
	 *
	 * @Assert\Image(maxSize = "4M", minWidth = 100, minHeight = 100)
	 */
	public $file;

	/**
	 * Is testing context ?
	 *
	 * @var boolean
	 */
	public $testingContext;



	/*
	 * Methods
	 */

	public function __construct()
	{
		$this->logo = 'default-logo.png';
		$this->countMembers = 0;
		$this->testingContext = false;
		$this->createdAt = new \DateTime();
	}

	public function __toString()
	{
		return $this->name;
	}

	/**
	 * Upload the photo
	 *
	 * @return boolean
	 */
	public function upload() {
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

		// Save the result
		$image->save(__DIR__ . '/../../../../../web/uploads/logos/'.$this->getLogin().'.png');

		$this->logo = $this->getLogin().'.png';

		return true;
	}

	/**
	 * @return boolean
	 */
	public function getIsOrga()
	{
		return true;
	}

	/**
	 * @return boolean
	 */
	public function getIsStudent()
	{
		return false;
	}

	/**
	 * @return Member[]|ArrayCollection
	 */
	public function getMemberships()
	{
		return $this->memberships;
	}

	/**
	 * @return string
	 */
	public function getAvatar()
	{
		return ($this->logo) ? $this->logo : 'default-logo.png';
	}

	/**
	 * @return string
	 */
	public function getFullName()
	{
		return $this->name;
	}

	/**
	 * @inheritDoc
	 */
	public function getUsername()
	{
		return $this->login;
	}

	/**
	 * @inheritDoc
	 */
	public function getSalt()
	{
		return md5($this->login.$this->contactMail);
	}

	/**
	 * @inheritDoc
	 */
	public function getPassword()
	{
		return substr($this->getSalt(), 0, 8);
	}

	/**
	 * @inheritDoc
	 */
	public function getRoles()
	{
		return array('ROLE_ORGA');
	}

	/**
	 * @inheritDoc
	 */
	public function eraseCredentials()
	{
	}

	/**
	 * @see \Serializable::serialize()
	 */
	public function serialize()
	{
		return serialize(array(
			$this->id, $this->login, $this->president, $this->name, $this->contactMail,
			$this->contactPhone, $this->description, $this->descriptionShort, $this->website,
			$this->logo, $this->countMembers, $this->createdAt, $this->updatedAt, $this->deletedAt,
		));
	}

	/**
	 * @see \Serializable::unserialize()
	 */
	public function unserialize($serialized)
	{
		list (
			$this->id, $this->login, $this->president, $this->name, $this->contactMail,
			$this->contactPhone, $this->description, $this->descriptionShort, $this->website,
			$this->logo, $this->countMembers, $this->createdAt, $this->updatedAt, $this->deletedAt,
		) = unserialize($serialized);
	}

	/**
	 * @param string $contactMail
	 * @return Organization
	 */
	public function setContactMail($contactMail)
	{
		$this->contactMail = $contactMail;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getContactMail()
	{
		return $this->contactMail;
	}

	/**
	 * @param string $contactPhone
	 * @return Organization
	 */
	public function setContactPhone($contactPhone)
	{
		$this->contactPhone = $contactPhone;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getContactPhone()
	{
		return $this->contactPhone;
	}

	/**
	 * @param string $description
	 * @return Organization
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
	 * @param string $descriptionShort
	 * @return Organization
	 */
	public function setDescriptionShort($descriptionShort)
	{
		$this->descriptionShort = $descriptionShort;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescriptionShort()
	{
		return $this->descriptionShort;
	}

	/**
	 * @return Organization
	 */
	public function addCountMembers()
	{
		$this->countMembers++;

		return $this;
	}

	/**
	 * @param int $countMembers
	 * @return Organization
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
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $login
	 * @return Organization
	 */
	public function setLogin($login)
	{
		$this->login = $login;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLogin()
	{
		return $this->login;
	}

	/**
	 * @param string $logo
	 * @return Organization
	 */
	public function setLogo($logo)
	{
		$this->logo = $logo;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLogo()
	{
		return $this->logo;
	}

	/**
	 * @param string $name
	 * @return Organization
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param \Etu\Core\UserBundle\Entity\User $president
	 * @return Organization
	 */
	public function setPresident($president)
	{
		$this->president = $president;

		return $this;
	}

	/**
	 * @return \Etu\Core\UserBundle\Entity\User
	 */
	public function getPresident()
	{
		return $this->president;
	}

	/**
	 * @param string $website
	 * @return Organization
	 */
	public function setWebsite($website)
	{
		$this->website = $website;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getWebsite()
	{
		return $this->website;
	}

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Organization
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
     * @return Organization
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
     * @return Organization
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
