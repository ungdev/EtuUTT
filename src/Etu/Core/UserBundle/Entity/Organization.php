<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\Point;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Organization
 *
 * @ORM\Table(name="etu_organizations")
 * @ORM\Entity
 */
class Organization implements UserInterface, \Serializable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="login", type="string", length=50)
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
	 * @ORM\Column(name="name", type="string", length=100)
	 */
	protected $name;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="contactMail", type="string", length=100, nullable=true)
	 */
	protected $contactMail;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="contactPhone", type="string", length=100, nullable=true)
	 */
	protected $contactPhone;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="description", type="text", nullable=true)
	 */
	protected $description;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="descriptionShort", type="string", length=200, nullable=true)
	 */
	protected $descriptionShort;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="website", type="string", length=100, nullable=true)
	 */
	protected $website;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="logo", type="string", length=100, nullable=true)
	 */
	protected $logo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="countMembers", type="integer", nullable=true)
	 */
	protected $countMembers;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="deleted", type="boolean")
	 */
	protected $deleted;

	/**
	 * Temporary variable to store uploaded file during photo update
	 *
	 * @var UploadedFile
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
		$this->deleted = false;
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
		$image->save(__DIR__ . '/../../../../../web/logos/'.$this->getLogin().'.png');

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
		return array('ROLE_ORGANIZATION');
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
			$this->id,
			$this->login,
			$this->password,
			$this->president,
			$this->name,
			$this->contactMail,
			$this->contactPhone,
			$this->contactElse,
			$this->website,
			$this->logo,
			$this->countMembers,
		));
	}

	/**
	 * @see \Serializable::unserialize()
	 */
	public function unserialize($serialized)
	{
		list (
			$this->id,
			$this->login,
			$this->password,
			$this->president,
			$this->name,
			$this->contactMail,
			$this->contactPhone,
			$this->contactElse,
			$this->website,
			$this->logo,
			$this->countMembers,
		) = unserialize($serialized);
	}

	/**
	 * @param $permission
	 * @return bool
	 */
	public function hasPermission($permission)
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function getIsAdmin()
	{
		return false;
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
	 * @param int $deleted
	 * @return Organization
	 */
	public function setDeleted($deleted)
	{
		$this->deleted = $deleted;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getDeleted()
	{
		return $this->deleted;
	}
}