<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table(name="etu_users", indexes={ @ORM\Index(name="search_idx", columns={"login", "mail"}) })
 * @ORM\Entity
 */
class User implements UserInterface, \Serializable
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
	 * @ORM\Column(name="password", type="string", length=100)
	 */
	protected $password;

    /**
     * @var integer
     *
     * @ORM\Column(name="studentId", type="integer")
     */
    protected $studentId;

    /**
     * @var string
     *
     * @ORM\Column(name="mail", type="string", length=100)
     */
    protected $mail;

    /**
     * @var string
     *
     * @ORM\Column(name="fullName", type="string", length=255)
     */
    protected $fullName;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255)
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="formation", type="string", length=255)
     */
    protected $formation;

    /**
     * @var string
     *
     * @ORM\Column(name="niveau", type="string", length=255)
     */
    protected $niveau;

    /**
     * @var string
     *
     * @ORM\Column(name="filiere", type="string", length=255)
     */
    protected $filiere;

    /**
     * @var string
     *
     * @ORM\Column(name="phoneNumber", type="string", length=255)
     */
    protected $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="room", type="string", length=255)
     */
    protected $room;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="avatar", type="string", length=255)
	 */
	protected $avatar;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="sex", type="string", length=50)
	 */
	protected $sex;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="nationality", type="string", length=50)
	 */
	protected $nationality;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="adress", type="string", length=100)
	 */
	protected $adress;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="postalCode", type="string", length=50)
	 */
	protected $postalCode;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="city", type="string", length=100)
	 */
	protected $city;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="country", type="string", length=50)
	 */
	protected $country;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="birthday", type="date")
	 */
	protected $birthday;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="age", type="integer")
	 */
	protected $age;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="personnalMail", type="string", length=100)
	 */
	protected $personnalMail;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="language", type="string", length=10)
	 */
	protected $language;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="isStudent", type="boolean")
	 */
	protected $isStudent;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="countNotifications", type="smallint")
	 */
	protected $countNotifications;

	/**
	 * @var string
	 *     > For trombi
	 *
	 * @ORM\Column(name="surnom", type="string", length=100)
	 */
	protected $surnom;

	/**
	 * @var string
	 *     > For trombi
	 *
	 * @ORM\Column(name="jadis", type="string", length=100)
	 */
	protected $jadis;

	/**
	 * @var string
	 *     > For trombi
	 *
	 * @ORM\Column(name="passions", type="text")
	 */
	protected $passions;

	/**
	 * @var string
	 *     > For trombi
	 *
	 * @ORM\Column(name="website", type="string", length=100)
	 */
	protected $website;

	/**
	 * LDAP root informations
	 *
	 * @var object
	 *
	 * @ORM\Column(name="ldapInformations", type="object")
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



	/*
	 * Methods
	 */


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
		return substr(md5($this->login), 0, 8);
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
			$this->age,
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
			$this->age,
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
	 * @param int $age
	 * @return User
	 */
	public function setAge($age)
	{
		$this->age = $age;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getAge()
	{
		return $this->age;
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
	 * @param boolean $countNotifications
	 * @return User
	 */
	public function setCountNotifications($countNotifications)
	{
		$this->countNotifications = $countNotifications;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getCountNotifications()
	{
		return $this->countNotifications;
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
}
