<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
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
	 * Password for who are not in CAS
	 *
	 * @var string
	 *
	 * @ORM\Column(name="password", type="string", length=100, nullable=true)
	 */
	protected $password;

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
	 * @ORM\Column(name="contactElse", type="text", nullable=true)
	 */
	protected $contactElse;

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
	protected $countMembers = 0;

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

		$image = $imagine->open($this->file->getPathname());

		$image->thumbnail(new Box(200, 200), Image::THUMBNAIL_OUTBOUND)->save(
			__DIR__ . '/../../../../../web/photos/'.$this->login.'.jpg'
		);

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
		return $this->password;
	}

	/**
	 * @param string $password
	 * @return Organization
	 */
	public function setPassword($password)
	{
		$this->password = $password;

		return $this;
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
	 * @param string $contactElse
	 * @return Organization
	 */
	public function setContactElse($contactElse)
	{
		$this->contactElse = $contactElse;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getContactElse()
	{
		return $this->contactElse;
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