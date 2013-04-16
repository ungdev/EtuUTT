<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Collection\UserOptionsCollection;
use Etu\Core\UserBundle\Ldap\Model\User as LdapUser;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table(
 *      name="etu_users",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="search", columns={"login", "mail"})}
 * )
 * @ORM\Entity
 */
class User implements UserInterface, \Serializable
{
	const SEX_MALE = 'male';
	const SEX_FEMALE = 'female';

	const PRIVACY_PUBLIC = 100;
	const PRIVACY_PRIVATE = 200;

	static public $branches = array(
		'ISI', 'MTE', 'SI', 'SIT', 'SM', 'SRT', 'TC'
	);

	static public $levels = array(
		'1', '2', '3', '4', '5', '6', '7', '8', '9', '10'
	);

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
     * @var integer
     *
     * @ORM\Column(name="studentId", type="integer", nullable=true)
     */
    protected $studentId;

    /**
     * @var string
     *
     * @ORM\Column(name="mail", type="string", length=100, nullable=true)
     */
    protected $mail;

    /**
     * @var string
     *
     * @ORM\Column(name="fullName", type="string", length=255, nullable=true)
     */
    protected $fullName;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="formation", type="string", length=255, nullable=true)
     */
    protected $formation;

    /**
     * @var string
     *
     * @ORM\Column(name="niveau", type="string", length=255, nullable=true)
     */
    protected $niveau;

    /**
     * @var string
     *
     * @ORM\Column(name="filiere", type="string", length=255, nullable=true)
     */
    protected $filiere;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="phoneNumber", type="string", length=30, nullable=true)
	 */
	protected $phoneNumber;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="phoneNumberPrivacy", type="integer")
	 */
	protected $phoneNumberPrivacy;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="room", type="string", length=255, nullable=true)
     */
    protected $room;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
	 */
	protected $avatar;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="sex", type="string", length=50, nullable=true)
	 */
	protected $sex;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="sexPrivacy", type="integer")
	 */
	protected $sexPrivacy;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="nationality", type="string", length=50, nullable=true)
	 */
	protected $nationality;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="nationalityPrivacy", type="integer")
	 */
	protected $nationalityPrivacy;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="adress", type="string", length=100, nullable=true)
	 */
	protected $adress;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="adressPrivacy", type="integer")
	 */
	protected $adressPrivacy;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="postalCode", type="string", length=50, nullable=true)
	 */
	protected $postalCode;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="postalCodePrivacy", type="integer")
	 */
	protected $postalCodePrivacy;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="city", type="string", length=100, nullable=true)
	 */
	protected $city;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="cityPrivacy", type="integer")
	 */
	protected $cityPrivacy;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="country", type="string", length=50, nullable=true)
	 */
	protected $country;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="countryPrivacy", type="integer")
	 */
	protected $countryPrivacy;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="birthday", type="date", nullable=true)
	 */
	protected $birthday;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="birthdayPrivacy", type="integer")
	 */
	protected $birthdayPrivacy;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="birthdayDisplayOnlyAge", type="boolean")
	 */
	protected $birthdayDisplayOnlyAge;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="personnalMail", type="string", length=100, nullable=true)
	 */
	protected $personnalMail;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="personnalMailPrivacy", type="integer")
	 */
	protected $personnalMailPrivacy;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="language", type="string", length=10, nullable=true)
	 */
	protected $language;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="isStudent", type="boolean")
	 */
	protected $isStudent;

	/**
	 * @var string
	 *     > For trombi
	 *
	 * @ORM\Column(name="surnom", type="string", length=100, nullable=true)
	 */
	protected $surnom;

	/**
	 * @var string
	 *     > For trombi
	 *
	 * @ORM\Column(name="jadis", type="text", nullable=true)
	 */
	protected $jadis;

	/**
	 * @var string
	 *     > For trombi
	 *
	 * @ORM\Column(name="passions", type="text", nullable=true)
	 */
	protected $passions;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="website", type="string", length=100, nullable=true)
	 */
	protected $website;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="facebook", type="string", length=100, nullable=true)
	 */
	protected $facebook;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="twitter", type="string", length=100, nullable=true)
	 */
	protected $twitter;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="linkedin", type="string", length=100, nullable=true)
	 */
	protected $linkedin;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="viadeo", type="string", length=100, nullable=true)
	 */
	protected $viadeo;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="uvs", type="string", length=100, nullable=true)
	 */
	protected $uvs;

	/**
	 * LDAP root informations
	 *
	 * @var object
	 *
	 * @ORM\Column(name="ldapInformations", type="object", nullable=true)
	 */
	protected $ldapInformations;

	/**
	 * Keep active even if not found in LDAP (for old students, external users, etc.)
	 *
	 * @var boolean
	 *
	 * @ORM\Column(name="keepActive", type="boolean")
	 */
	protected $keepActive;

	/**
	 * Permissions on EtuUTT
	 *
	 * @var array
	 *
	 * @ORM\Column(name="permissions", type="array")
	 */
	protected $permissions = array();

	/**
	 * If the user is admin, he has all permissions, even from the new modules
	 *
	 * @var boolean
	 *
	 * @ORM\Column(name="isAdmin", type="boolean")
	 */
	protected $isAdmin;

	/**
	 * Badges
	 *
	 * @var array
	 *
	 * @ORM\Column(name="badges", type="array")
	 */
	protected $badges = array();

	/**
	 * Modules options (no format, just an array stored to be sued by modules as they want)
	 *
	 * @var UserOptionsCollection
	 *
	 * @ORM\Column(name="options", type="object")
	 */
	protected $options;

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
		$this->keepActive = false;
		$this->isStudent = true;
		$this->isAdmin = false;
		$this->avatar = 'default-avatar.png';
		$this->phoneNumberPrivacy = self::PRIVACY_PUBLIC;
		$this->sexPrivacy = self::PRIVACY_PUBLIC;
		$this->nationalityPrivacy = self::PRIVACY_PUBLIC;
		$this->adressPrivacy = self::PRIVACY_PUBLIC;
		$this->postalCodePrivacy = self::PRIVACY_PUBLIC;
		$this->cityPrivacy = self::PRIVACY_PUBLIC;
		$this->countryPrivacy = self::PRIVACY_PUBLIC;
		$this->birthdayPrivacy = self::PRIVACY_PUBLIC;
		$this->birthdayDisplayOnlyAge = false;
		$this->personnalMailPrivacy = self::PRIVACY_PUBLIC;
		$this->options = new UserOptionsCollection();
		$this->badges = array();
		$this->permissions = array();
		$this->ldapInformations = new LdapUser();
		$this->uvs = '';
	}

	public function __toString()
	{
		return $this->fullName;
	}

	/**
	 * Return avilable branches and levels for forms
	 *
	 * @return array
	 */
	static public function availableBranches()
	{
		$result = array();

		foreach (self::$branches as $branch) {
			foreach (self::$levels as $level) {
				$result[] = $branch.$level;
			}
		}

		return $result;
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
			__DIR__ . '/../../../../../web/photos/'.$this->getLogin().'.jpg'
		);

		$this->avatar = $this->getLogin().'.jpg';

		return true;
	}

	/**
	 * @return boolean
	 */
	public function getIsOrga()
	{
		return false;
	}

	/**
	 * @return integer
	 */
	public function getProfileCompletion()
	{
		$infos = array(
			$this->phoneNumber, $this->sex, $this->nationality, $this->adress, $this->postalCode, $this->city,
			$this->country, $this->birthday, $this->personnalMail, $this->surnom, $this->jadis, $this->passions
		);

		$completion = 0;
		$count = 0;

		foreach ($infos as $value) {
			$count++;

			if (! empty($value)) {
				$completion++;
			}
		}

		return round($completion / $count, 2) * 100;
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
		return md5($this->login.$this->mail);
	}

	/**
	 * @inheritDoc
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @inheritDoc
	 */
	public function getRoles()
	{
		return array('ROLE_USER');
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
			$this->studentId,
			$this->mail,
			$this->fullName,
			$this->firstName,
			$this->lastName,
			$this->formation,
			$this->niveau,
			$this->filiere,
			$this->phoneNumber,
			$this->title,
			$this->room,
			$this->avatar,
			$this->sex,
			$this->nationality,
			$this->adress,
			$this->postalCode,
			$this->city,
			$this->country,
			$this->birthday,
			$this->personnalMail,
			$this->language,
			$this->isStudent,
			$this->surnom,
			$this->jadis,
			$this->passions,
			$this->website,
			$this->ldapInformations,
			$this->keepActive,
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
			$this->studentId,
			$this->mail,
			$this->fullName,
			$this->firstName,
			$this->lastName,
			$this->formation,
			$this->niveau,
			$this->filiere,
			$this->phoneNumber,
			$this->title,
			$this->room,
			$this->avatar,
			$this->sex,
			$this->nationality,
			$this->adress,
			$this->postalCode,
			$this->city,
			$this->country,
			$this->birthday,
			$this->personnalMail,
			$this->language,
			$this->isStudent,
			$this->surnom,
			$this->jadis,
			$this->passions,
			$this->website,
			$this->ldapInformations,
			$this->keepActive,
		) = unserialize($serialized);
	}

	/**
	 * @param string $adress
	 * @return User
	 */
	public function setAdress($adress)
	{
		$this->adress = $adress;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAdress()
	{
		return $this->adress;
	}

	/**
	 * @param string $avatar
	 * @return User
	 */
	public function setAvatar($avatar)
	{
		$this->avatar = $avatar;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAvatar()
	{
		return $this->avatar;
	}

	/**
	 * @param \DateTime $birthday
	 * @return User
	 */
	public function setBirthday($birthday)
	{
		$this->birthday = $birthday;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getBirthday()
	{
		return $this->birthday;
	}

	/**
	 * @return integer
	 */
	public function getAge()
	{
		return $this->birthday->diff(new \DateTime())->y;
	}

	/**
	 * @param string $city
	 * @return User
	 */
	public function setCity($city)
	{
		$this->city = $city;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 * @param string $country
	 * @return User
	 */
	public function setCountry($country)
	{
		$this->country = $country;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * @param string $filiere
	 * @return User
	 */
	public function setFiliere($filiere)
	{
		if ($filiere == 'NC') {
			$filiere = null;
		}

		$this->filiere = $filiere;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFiliere()
	{
		return $this->filiere;
	}

	/**
	 * @param string $firstName
	 * @return User
	 */
	public function setFirstName($firstName)
	{
		$this->firstName = $firstName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * @param string $formation
	 * @return User
	 */
	public function setFormation($formation)
	{
		if ($formation == 'NC') {
			$formation = null;
		}

		$this->formation = $formation;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFormation()
	{
		return $this->formation;
	}

	/**
	 * @param string $fullName
	 * @return User
	 */
	public function setFullName($fullName)
	{
		$this->fullName = $fullName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFullName()
	{
		return $this->fullName;
	}

	/**
	 * @param int $id
	 * @return User
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
	 * @param boolean $isStudent
	 * @return User
	 */
	public function setIsStudent($isStudent)
	{
		$this->isStudent = $isStudent;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getIsStudent()
	{
		return $this->isStudent;
	}

	/**
	 * @param string $jadis
	 * @return User
	 */
	public function setJadis($jadis)
	{
		$this->jadis = $jadis;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getJadis()
	{
		return $this->jadis;
	}

	/**
	 * @param boolean $keepActive
	 * @return User
	 */
	public function setKeepActive($keepActive)
	{
		$this->keepActive = $keepActive;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getKeepActive()
	{
		return $this->keepActive;
	}

	/**
	 * @param string $language
	 * @return User
	 */
	public function setLanguage($language)
	{
		$this->language = $language;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * @param string $lastName
	 * @return User
	 */
	public function setLastName($lastName)
	{
		$this->lastName = $lastName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLastName()
	{
		return $this->lastName;
	}

	/**
	 * @param object $ldapInformations
	 * @return User
	 */
	public function setLdapInformations($ldapInformations)
	{
		$this->ldapInformations = $ldapInformations;

		return $this;
	}

	/**
	 * @return object
	 */
	public function getLdapInformations()
	{
		return $this->ldapInformations;
	}

	/**
	 * @param string $login
	 * @return User
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
	 * @param string $mail
	 * @return User
	 */
	public function setMail($mail)
	{
		$this->mail = $mail;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getMail()
	{
		return $this->mail;
	}

	/**
	 * @param string $nationality
	 * @return User
	 */
	public function setNationality($nationality)
	{
		$this->nationality = $nationality;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getNationality()
	{
		return $this->nationality;
	}

	/**
	 * @param string $niveau
	 * @return User
	 */
	public function setNiveau($niveau)
	{
		if ($niveau == 'NC') {
			$niveau = null;
		}

		$this->niveau = $niveau;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getNiveau()
	{
		return $this->niveau;
	}

	/**
	 * @param string $passions
	 * @return User
	 */
	public function setPassions($passions)
	{
		$this->passions = $passions;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPassions()
	{
		return $this->passions;
	}

	/**
	 * @param string $personnalMail
	 * @return User
	 */
	public function setPersonnalMail($personnalMail)
	{
		$this->personnalMail = $personnalMail;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPersonnalMail()
	{
		return $this->personnalMail;
	}

	/**
	 * @param string $phoneNumber
	 * @return User
	 */
	public function setPhoneNumber($phoneNumber)
	{
		if ($phoneNumber == 'NC') {
			$phoneNumber = null;
		}

		$this->phoneNumber = $phoneNumber;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPhoneNumber()
	{
		return $this->phoneNumber;
	}

	/**
	 * @param string $postalCode
	 * @return User
	 */
	public function setPostalCode($postalCode)
	{
		$this->postalCode = $postalCode;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPostalCode()
	{
		return $this->postalCode;
	}

	/**
	 * @param string $room
	 * @return User
	 */
	public function setRoom($room)
	{
		if ($room == 'NC') {
			$room = null;
		}

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
	 * @param string $sex
	 * @return User
	 */
	public function setSex($sex)
	{
		$this->sex = $sex;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSex()
	{
		return $this->sex;
	}

	/**
	 * @param int $studentId
	 * @return User
	 */
	public function setStudentId($studentId)
	{
		if ($studentId == 'NC') {
			$studentId = null;
		}

		$this->studentId = $studentId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getStudentId()
	{
		return $this->studentId;
	}

	/**
	 * @param string $surnom
	 * @return User
	 */
	public function setSurnom($surnom)
	{
		$this->surnom = $surnom;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSurnom()
	{
		return $this->surnom;
	}

	/**
	 * @param string $title
	 * @return User
	 */
	public function setTitle($title)
	{
		if ($title == 'NC') {
			$title = null;
		}

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
	 * @param string $website
	 * @return User
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
	 * @param boolean $permissions
	 * @return User
	 */
	public function setPermissions($permissions)
	{
		$this->permissions = $permissions;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}

	/**
	 * @param string $permission
	 * @return bool
	 */
	public function hasPermission($permission)
	{
		return in_array($permission, $this->permissions);
	}

	/**
	 * @param string $permission
	 * @return User
	 */
	public function addPermission($permission)
	{
		$this->permissions[] = $permission;

		return $this;
	}

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set phoneNumberPrivacy
     *
     * @param integer $phoneNumberPrivacy
     * @return User
     */
    public function setPhoneNumberPrivacy($phoneNumberPrivacy)
    {
        $this->phoneNumberPrivacy = $phoneNumberPrivacy;

        return $this;
    }

    /**
     * Get phoneNumberPrivacy
     *
     * @return integer
     */
    public function getPhoneNumberPrivacy()
    {
        return $this->phoneNumberPrivacy;
    }

    /**
     * Set sexPrivacy
     *
     * @param integer $sexPrivacy
     * @return User
     */
    public function setSexPrivacy($sexPrivacy)
    {
        $this->sexPrivacy = $sexPrivacy;

        return $this;
    }

    /**
     * Get sexPrivacy
     *
     * @return integer
     */
    public function getSexPrivacy()
    {
        return $this->sexPrivacy;
    }

    /**
     * Set nationalityPrivacy
     *
     * @param integer $nationalityPrivacy
     * @return User
     */
    public function setNationalityPrivacy($nationalityPrivacy)
    {
        $this->nationalityPrivacy = $nationalityPrivacy;

        return $this;
    }

    /**
     * Get nationalityPrivacy
     *
     * @return integer
     */
    public function getNationalityPrivacy()
    {
        return $this->nationalityPrivacy;
    }

    /**
     * Set adressPrivacy
     *
     * @param integer $adressPrivacy
     * @return User
     */
    public function setAdressPrivacy($adressPrivacy)
    {
        $this->adressPrivacy = $adressPrivacy;

        return $this;
    }

    /**
     * Get adressPrivacy
     *
     * @return integer
     */
    public function getAdressPrivacy()
    {
        return $this->adressPrivacy;
    }

    /**
     * Set postalCodePrivacy
     *
     * @param integer $postalCodePrivacy
     * @return User
     */
    public function setPostalCodePrivacy($postalCodePrivacy)
    {
        $this->postalCodePrivacy = $postalCodePrivacy;

        return $this;
    }

    /**
     * Get postalCodePrivacy
     *
     * @return integer
     */
    public function getPostalCodePrivacy()
    {
        return $this->postalCodePrivacy;
    }

    /**
     * Set cityPrivacy
     *
     * @param integer $cityPrivacy
     * @return User
     */
    public function setCityPrivacy($cityPrivacy)
    {
        $this->cityPrivacy = $cityPrivacy;

        return $this;
    }

    /**
     * Get cityPrivacy
     *
     * @return integer
     */
    public function getCityPrivacy()
    {
        return $this->cityPrivacy;
    }

    /**
     * Set countryPrivacy
     *
     * @param integer $countryPrivacy
     * @return User
     */
    public function setCountryPrivacy($countryPrivacy)
    {
        $this->countryPrivacy = $countryPrivacy;

        return $this;
    }

    /**
     * Get countryPrivacy
     *
     * @return integer
     */
    public function getCountryPrivacy()
    {
        return $this->countryPrivacy;
    }

    /**
     * Set birthdayPrivacy
     *
     * @param integer $birthdayPrivacy
     * @return User
     */
    public function setBirthdayPrivacy($birthdayPrivacy)
    {
        $this->birthdayPrivacy = $birthdayPrivacy;

        return $this;
    }

    /**
     * Get birthdayPrivacy
     *
     * @return integer
     */
    public function getBirthdayPrivacy()
    {
        return $this->birthdayPrivacy;
    }

    /**
     * Set birthdayDisplayOnlyAge
     *
     * @param boolean $birthdayDisplayOnlyAge
     * @return User
     */
    public function setBirthdayDisplayOnlyAge($birthdayDisplayOnlyAge)
    {
        $this->birthdayDisplayOnlyAge = $birthdayDisplayOnlyAge;

        return $this;
    }

    /**
     * Get birthdayDisplayOnlyAge
     *
     * @return boolean
     */
    public function getBirthdayDisplayOnlyAge()
    {
        return $this->birthdayDisplayOnlyAge;
    }

    /**
     * Set personnalMailPrivacy
     *
     * @param integer $personnalMailPrivacy
     * @return User
     */
    public function setPersonnalMailPrivacy($personnalMailPrivacy)
    {
        $this->personnalMailPrivacy = $personnalMailPrivacy;

        return $this;
    }

    /**
     * Get personnalMailPrivacy
     *
     * @return integer
     */
    public function getPersonnalMailPrivacy()
    {
        return $this->personnalMailPrivacy;
    }

	/**
	 * @param array $badges
	 * @return User
	 */
	public function setBadges(array $badges)
	{
		$this->badges = $badges;

		return $this;
	}

	/**
	 * @param string $badgeName
	 * @return boolean
	 */
	public function hasBadge($badgeName)
	{
		return in_array($badgeName, $this->badges);
	}

	/**
	 * @param string $badgeName
	 * @return User
	 */
	public function addBadge($badgeName)
	{
		$this->badges[] = $badgeName;

		return $this;
	}

	/**
	 * @param string $badgeName
	 * @return User
	 */
	public function removeBadge($badgeName)
	{
		if (($key = array_search($badgeName, $this->badges)) !== false) {
			unset($this->badges[$key]);
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function getBadges()
	{
		return $this->badges;
	}

	/**
	 * @param string $facebook
	 * @return User
	 */
	public function setFacebook($facebook)
	{
		$this->facebook = $facebook;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFacebook()
	{
		return $this->facebook;
	}

	/**
	 * @param string $linkedin
	 * @return User
	 */
	public function setLinkedin($linkedin)
	{
		$this->linkedin = $linkedin;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLinkedin()
	{
		return $this->linkedin;
	}

	/**
	 * @param string $twitter
	 * @return User
	 */
	public function setTwitter($twitter)
	{
		$this->twitter = $twitter;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTwitter()
	{
		return $this->twitter;
	}

	/**
	 * @param string $viadeo
	 * @return User
	 */
	public function setViadeo($viadeo)
	{
		$this->viadeo = $viadeo;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getViadeo()
	{
		return $this->viadeo;
	}

	/**
	 * @param boolean $isAdmin
	 * @return User
	 */
	public function setIsAdmin($isAdmin)
	{
		$this->isAdmin = $isAdmin;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getIsAdmin()
	{
		return $this->isAdmin;
	}

	/**
	 * @param string $uvs
	 * @return User
	 */
	public function setUvs($uvs)
	{
		$this->uvs = $uvs;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUvs()
	{
		return $this->uvs;
	}

	/**
	 * @return string
	 */
	public function displayUvs()
	{
		return implode(', ', explode('|', $this->uvs));
	}

	/**
	 * @return \Etu\Core\UserBundle\Collection\UserOptionsCollection
	 */
	public function getOptions()
	{
		return $this->options;
	}
}