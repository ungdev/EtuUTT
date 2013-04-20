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
 * @ORM\Table(name="etu_organizations", indexes={ @ORM\Index(name="search", columns={"login", "name"}) })
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
	 * Temporary variable to store uploaded file during photo update
	 *
	 * @var UploadedFile
	 */
	public $file;



	/*
	 * Methods
	 */

	public function __construct()
	{
		$this->logo = 'default-logo.png';
		$this->countMembers = 1;
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

		// Upload and resize
		$imagine = new Imagine();

		$logo = $imagine->create(new Box(200, 200), new Color('FFF'));
		$image = $imagine->open($this->file->getPathname())->thumbnail(new Box(200, 200), Image::THUMBNAIL_INSET);

		if ($image->getSize()->getWidth() < 200) {
			$point = new Point((200 - $image->getSize()->getWidth()) / 2, 0);
		} elseif ($image->getSize()->getHeight() < 200) {
			$point = new Point(0, (200 - $image->getSize()->getHeight()) / 2);
		} else {
			$point = new Point(0, 0);
		}

		$logo->paste($image, $point);
		$logo->save(__DIR__ . '/../../../../../web/logos/'.$this->login.'.jpg');

		$this->logo = $this->login.'.jpg';

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
}