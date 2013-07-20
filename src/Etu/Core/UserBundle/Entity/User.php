<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Model\Badge;
use Gedmo\Mapping\Annotation as Gedmo;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Etu\Core\CoreBundle\Framework\Definition\Permission;
use Etu\Core\CoreBundle\Framework\EtuKernel;
use Etu\Core\CoreBundle\Framework\Module\PermissionsCollection;
use Etu\Core\UserBundle\Collection\UserOptionsCollection;
use Etu\Core\UserBundle\Ldap\Model\User as LdapUser;

use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\Point;

/**
 * User
 *
 * @ORM\Table(
 *      name="etu_users",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="search", columns={"login", "mail"})}
 * )
 * @ORM\Entity()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class User implements UserInterface, \Serializable
{
	const SEX_MALE = 'male';
	const SEX_FEMALE = 'female';

	const PRIVACY_PUBLIC = 100;
	const PRIVACY_PRIVATE = 200;

	static public $branches = array(
		'ISI' => 'ISI', 'MTE' => 'MTE', 'SI' => 'SI',
		'SIT' => 'SIT', 'SM' => 'SM', 'SRT' => 'SRT',
		'TC' => 'TC'
	);

	static public $levels = array(
		'1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5',
		'6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10'
	);

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
	 */
	protected $login;

	/**
	 * Password for who are not in CAS
	 *
	 * @var string
	 *
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $password;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $studentId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $mail;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $fullName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(min = "2", max = "50")
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(min = "2", max = "50")
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $formation;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $niveau;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $filiere;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=30, nullable=true)
	 * @Assert\Regex("/^0[1-68]([-. ]?[0-9]{2}){4}$/")
	 */
	protected $phoneNumber;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 * @Assert\NotBlank()
	 */
	protected $phoneNumberPrivacy;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(min = "2", max = "50")
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(min = "2", max = "50")
     */
    protected $room;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $avatar;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=50, nullable=true)
	 */
	protected $sex;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 * @Assert\NotBlank()
	 */
	protected $sexPrivacy;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=50, nullable=true)
	 * @Assert\Length(min = "2", max = "50")
	 */
	protected $nationality;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 * @Assert\NotBlank()
	 */
	protected $nationalityPrivacy;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=100, nullable=true)
	 * @Assert\Length(min = "2", max = "100")
	 */
	protected $adress;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 * @Assert\NotBlank()
	 */
	protected $adressPrivacy;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=50, nullable=true)
	 */
	protected $postalCode;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 * @Assert\NotBlank()
	 */
	protected $postalCodePrivacy;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=100, nullable=true)
	 * @Assert\Length(min = "2", max = "50")
	 */
	protected $city;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 * @Assert\NotBlank()
	 */
	protected $cityPrivacy;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=50, nullable=true)
	 * @Assert\Length(min = "2", max = "50")
	 */
	protected $country;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 * @Assert\NotBlank()
	 */
	protected $countryPrivacy;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=true)
	 * @Assert\Date()
	 */
	protected $birthday;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 * @Assert\NotBlank()
	 */
	protected $birthdayPrivacy;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $birthdayDisplayOnlyAge;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=100, nullable=true)
	 * @Assert\Email()
	 */
	protected $personnalMail;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 * @Assert\NotBlank()
	 */
	protected $personnalMailPrivacy;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */
	protected $language;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $isStudent;

	/**
	 * @var string
	 *     > For trombi
	 *
	 * @ORM\Column(type="string", length=50, nullable=true)
	 * @Assert\Length(max = "50")
	 */
	protected $surnom;

	/**
	 * @var string
	 *     > For trombi
	 *
	 * @ORM\Column(type="text", nullable=true)
	 * @Assert\Length(min = "15")
	 */
	protected $jadis;

	/**
	 * @var string
	 *     > For trombi
	 *
	 * @ORM\Column(type="text", nullable=true)
	 * @Assert\Length(min = "15")
	 */
	protected $passions;

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
	 * @Assert\Url()
	 */
	protected $facebook;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=100, nullable=true)
	 * @Assert\Url()
	 */
	protected $twitter;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=100, nullable=true)
	 * @Assert\Url()
	 */
	protected $linkedin;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=100, nullable=true)
	 * @Assert\Url()
	 */
	protected $viadeo;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $uvs;

	/**
	 * Keep active even if not found in LDAP (for old students, external users, etc.)
	 *
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $keepActive;

	/**
	 * Added permissions
	 *      => used for administration permissions
	 *
	 * @var array
	 *
	 * @ORM\Column(type="array")
	 */
	protected $permissions = array();

	/**
	 * Removed permissions
	 *      => used for classic permissions that everyone but this specific user have
	 *
	 * @var array
	 *
	 * @ORM\Column(type="array")
	 */
	protected $removedPermissions = array();

	/**
	 * If the user is admin, he has all permissions, even from the new modules
	 *
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $isAdmin;

	/**
	 * Read-only mode enabled fo this user?
	 *
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $isReadOnly;

	/**
	 * Read-only expiration date
	 *
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime")
	 */
	protected $readOnlyExpirationDate;

	/**
	 * Badges
	 *
	 * @var array
	 *
	 * @ORM\Column(type="array")
	 */
	protected $badges = array();

	/**
	 * Modules options (no format, just an array stored to be sued by modules as they want)
	 *
	 * @var array
	 *
	 * @ORM\Column(type="array")
	 */
	protected $options;

	/**
	 * Last visit date
	 *
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime")
	 */
	protected $lastVisitHome;

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
	 * @ORM\OneToMany(targetEntity="\Etu\Core\UserBundle\Entity\Member", mappedBy="user")
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
		$this->testingContext = false;
		$this->keepActive = false;
		$this->isStudent = true;
		$this->isReadOnly = false;
		$this->readOnlyExpirationDate = new \DateTime();
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
		$this->lastVisitHome = new \DateTime('0000-00-00 00:00:00');
		$this->createdAt = new \DateTime();
	}

	public function __toString()
	{
		return $this->fullName;
	}

	/**
	 * Return available branches and levels for forms
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

		// Save the result
		$image->save(__DIR__ . '/../../../../../web/photos/'.$this->getLogin().'.png');

		$this->avatar = $this->getLogin().'.png';

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
			$this->country, $this->birthday, $this->personnalMail
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
	 * @return integer
	 */
	public function getTrombiCompletion()
	{
		$infos = array($this->surnom, $this->jadis, $this->passions);

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
			$this->phoneNumberPrivacy,
			$this->title,
			$this->room,
			$this->avatar,
			$this->sex,
			$this->sexPrivacy,
			$this->nationality,
			$this->nationalityPrivacy,
			$this->adress,
			$this->adressPrivacy,
			$this->postalCode,
			$this->postalCodePrivacy,
			$this->city,
			$this->cityPrivacy,
			$this->country,
			$this->countryPrivacy,
			$this->birthday,
			$this->birthdayPrivacy,
			$this->birthdayDisplayOnlyAge,
			$this->personnalMail,
			$this->personnalMailPrivacy,
			$this->language,
			$this->isStudent,
			$this->surnom,
			$this->jadis,
			$this->passions,
			$this->website,
			$this->facebook,
			$this->twitter,
			$this->linkedin,
			$this->viadeo,
			$this->uvs,
			$this->keepActive,
			$this->permissions,
			$this->removedPermissions,
			$this->badges,
			$this->options,
			$this->lastVisitHome,
			$this->createdAt,
			$this->updatedAt,
			$this->deletedAt,
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
			$this->phoneNumberPrivacy,
			$this->title,
			$this->room,
			$this->avatar,
			$this->sex,
			$this->sexPrivacy,
			$this->nationality,
			$this->nationalityPrivacy,
			$this->adress,
			$this->adressPrivacy,
			$this->postalCode,
			$this->postalCodePrivacy,
			$this->city,
			$this->cityPrivacy,
			$this->country,
			$this->countryPrivacy,
			$this->birthday,
			$this->birthdayPrivacy,
			$this->birthdayDisplayOnlyAge,
			$this->personnalMail,
			$this->personnalMailPrivacy,
			$this->language,
			$this->isStudent,
			$this->surnom,
			$this->jadis,
			$this->passions,
			$this->website,
			$this->facebook,
			$this->twitter,
			$this->linkedin,
			$this->viadeo,
			$this->uvs,
			$this->keepActive,
			$this->permissions,
			$this->removedPermissions,
			$this->badges,
			$this->options,
			$this->lastVisitHome,
			$this->createdAt,
			$this->updatedAt,
			$this->deletedAt,
		) = unserialize($serialized);
	}

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set login
     *
     * @param string $login
     * @return User
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
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
     * Get studentId
     *
     * @return integer
     */
    public function getStudentId()
    {
        return $this->studentId;
    }

    /**
     * Set mail
     *
     * @param string $mail
     * @return User
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail
     *
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

	/**
	 * @param string $fullName
	 * @return User
	 */
	public function setFullName($fullName)
	{
		$this->fullName = $fullName;

		$parts = explode(' ', $fullName);

		$this->firstName = $parts[0];
		unset($parts[0]);

		if (! empty($parts)) {
			$this->lastName = implode(' ', $parts);
		}

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
     * Set firstName
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
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
     * Get formation
     *
     * @return string
     */
    public function getFormation()
    {
        return $this->formation;
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
     * Get niveau
     *
     * @return string
     */
    public function getNiveau()
    {
        return $this->niveau;
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
     * Get filiere
     *
     * @return string
     */
    public function getFiliere()
    {
        return $this->filiere;
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
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
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
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
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
     * Get room
     *
     * @return string
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set sex
     *
     * @param string $sex
     * @return User
     */
    public function setSex($sex)
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * Get sex
     *
     * @return string
     */
    public function getSex()
    {
        return $this->sex;
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
     * Set nationality
     *
     * @param string $nationality
     * @return User
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;

        return $this;
    }

    /**
     * Get nationality
     *
     * @return string
     */
    public function getNationality()
    {
        return $this->nationality;
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
     * Set adress
     *
     * @param string $adress
     * @return User
     */
    public function setAdress($adress)
    {
        $this->adress = $adress;

        return $this;
    }

    /**
     * Get adress
     *
     * @return string
     */
    public function getAdress()
    {
        return $this->adress;
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
     * Set postalCode
     *
     * @param string $postalCode
     * @return User
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
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
     * Set city
     *
     * @param string $city
     * @return User
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
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
     * Set country
     *
     * @param string $country
     * @return User
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
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
     * Set birthday
     *
     * @param \DateTime $birthday
     * @return User
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
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
     * Set personnalMail
     *
     * @param string $personnalMail
     * @return User
     */
    public function setPersonnalMail($personnalMail)
    {
        $this->personnalMail = $personnalMail;

        return $this;
    }

    /**
     * Get personnalMail
     *
     * @return string
     */
    public function getPersonnalMail()
    {
        return $this->personnalMail;
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
     * Set language
     *
     * @param string $language
     * @return User
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set isStudent
     *
     * @param boolean $isStudent
     * @return User
     */
    public function setIsStudent($isStudent)
    {
        $this->isStudent = $isStudent;

        return $this;
    }

    /**
     * Get isStudent
     *
     * @return boolean
     */
    public function getIsStudent()
    {
        return $this->isStudent;
    }

    /**
     * Set surnom
     *
     * @param string $surnom
     * @return User
     */
    public function setSurnom($surnom)
    {
        $this->surnom = $surnom;

        return $this;
    }

    /**
     * Get surnom
     *
     * @return string
     */
    public function getSurnom()
    {
        return $this->surnom;
    }

    /**
     * Set jadis
     *
     * @param string $jadis
     * @return User
     */
    public function setJadis($jadis)
    {
        $this->jadis = $jadis;

        return $this;
    }

    /**
     * Get jadis
     *
     * @return string
     */
    public function getJadis()
    {
        return $this->jadis;
    }

    /**
     * Set passions
     *
     * @param string $passions
     * @return User
     */
    public function setPassions($passions)
    {
        $this->passions = $passions;

        return $this;
    }

    /**
     * Get passions
     *
     * @return string
     */
    public function getPassions()
    {
        return $this->passions;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return User
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set facebook
     *
     * @param string $facebook
     * @return User
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * Get facebook
     *
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set twitter
     *
     * @param string $twitter
     * @return User
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;

        return $this;
    }

    /**
     * Get twitter
     *
     * @return string
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * Set linkedin
     *
     * @param string $linkedin
     * @return User
     */
    public function setLinkedin($linkedin)
    {
        $this->linkedin = $linkedin;

        return $this;
    }

    /**
     * Get linkedin
     *
     * @return string
     */
    public function getLinkedin()
    {
        return $this->linkedin;
    }

    /**
     * Set viadeo
     *
     * @param string $viadeo
     * @return User
     */
    public function setViadeo($viadeo)
    {
        $this->viadeo = $viadeo;

        return $this;
    }

    /**
     * Get viadeo
     *
     * @return string
     */
    public function getViadeo()
    {
        return $this->viadeo;
    }

    /**
     * Set uvs
     *
     * @param string $uvs
     * @return User
     */
    public function setUvs($uvs)
    {
        $this->uvs = $uvs;

        return $this;
    }

    /**
     * Get uvs
     *
     * @return string
     */
    public function getUvs()
    {
        return $this->uvs;
    }

	/**
	 * @return array
	 */
	public function getUvsList()
	{
		return explode('|', $this->uvs);
	}

	/**
	 * @return string
	 */
	public function displayUvs()
	{
		return implode(', ', $this->getUvsList());
	}

    /**
     * Set ldapInformations
     *
     * @param \stdClass $ldapInformations
     * @return User
     */
    public function setLdapInformations($ldapInformations)
    {
        return $this;
    }

    /**
     * Get ldapInformations
     *
     * @return \stdClass
     */
    public function getLdapInformations()
    {
        return new \stdClass();
    }

    /**
     * Set keepActive
     *
     * @param boolean $keepActive
     * @return User
     */
    public function setKeepActive($keepActive)
    {
        $this->keepActive = $keepActive;

        return $this;
    }

    /**
     * Get keepActive
     *
     * @return boolean
     */
    public function getKeepActive()
    {
        return $this->keepActive;
    }

	/**
	 * @return boolean
	 */
	public function getIsExternal()
	{
		return $this->keepActive;
	}

    /**
     * Set permissions
     *
     * @param array $permissions
     * @return User
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Get permissions
     *
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

	/**
	 * @param string $permissionName
	 * @param bool $defaultEnabled
	 * @return bool
	 */
	public function hasPermission($permissionName, $defaultEnabled = false)
	{
		if ($this->isAdmin) {
			return true;
		}

		if (EtuKernel::getFrozenPermissions() instanceof PermissionsCollection) {
			$permission = EtuKernel::getFrozenPermissions()->get($permissionName);

			if ($permission instanceof Permission) {
				$defaultEnabled = $permission->getDefaultEnabled();
			}
		}

		if (! $defaultEnabled) {
			return in_array($permissionName, $this->permissions);
		}

		return ! in_array($permissionName, $this->removedPermissions);
	}

	/**
	 * @param string $permission
	 * @return User
	 */
	public function addPermission($permission)
	{
		if (! in_array($permission, $this->permissions)) {
			$this->permissions[] = $permission;
		}

		return $this;
	}

	/**
	 * @param string $permission
	 * @return User
	 */
	public function removePermission($permission)
	{
		if (($key = array_search($permission, $this->permissions)) !== false) {
			unset($this->permissions[$key]);
		}

		return $this;
	}

    /**
     * Set removedPermissions
     *
     * @param array $removedPermissions
     * @return User
     */
    public function setRemovedPermissions($removedPermissions)
    {
        $this->removedPermissions = $removedPermissions;

        return $this;
    }

    /**
     * Get removedPermissions
     *
     * @return array
     */
    public function getRemovedPermissions()
    {
        return $this->removedPermissions;
    }

	/**
	 * @param string $permission
	 * @return bool
	 */
	public function hasRemovedPermission($permission)
	{
		return $this->isAdmin || in_array($permission, $this->removedPermissions);
	}

	/**
	 * @param string $permission
	 * @return User
	 */
	public function addRemovedPermission($permission)
	{
		if (! in_array($permission, $this->removedPermissions)) {
			$this->removedPermissions[] = $permission;
		}

		return $this;
	}

	/**
	 * @param string $permission
	 * @return User
	 */
	public function removeRemovedPermission($permission)
	{
		if (($key = array_search($permission, $this->removedPermissions)) !== false) {
			unset($this->removedPermissions[$key]);
		}

		return $this;
	}

    /**
     * Set isAdmin
     *
     * @param boolean $isAdmin
     * @return User
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    /**
     * Get isAdmin
     *
     * @return boolean
     */
    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * Set isReadOnly
     *
     * @param boolean $isReadOnly
     * @return User
     */
    public function setIsReadOnly($isReadOnly)
    {
        $this->isReadOnly = $isReadOnly;

        return $this;
    }

    /**
     * Get isReadOnly
     *
     * @return boolean
     */
    public function getIsReadOnly()
    {
        return $this->isReadOnly;
    }

    /**
     * Set readOnlyExpirationDate
     *
     * @param \DateTime $readOnlyExpirationDate
     * @return User
     */
    public function setReadOnlyExpirationDate($readOnlyExpirationDate)
    {
        $this->readOnlyExpirationDate = $readOnlyExpirationDate;

        return $this;
    }

	/**
	 * @param string $dateString
	 * @return User
	 */
	public function setReadOnlyPeriod($dateString)
	{
		$interval = \DateInterval::createFromDateString($dateString);

		$date = new \DateTime();
		$date->add($interval);

		$this->setReadOnlyExpirationDate($date);

		return $this;
	}

    /**
     * Get readOnlyExpirationDate
     *
     * @return \DateTime
     */
    public function getReadOnlyExpirationDate()
    {
        return $this->readOnlyExpirationDate;
    }

    /**
     * Set badges
     *
     * @param array $badges
     * @return User
     */
    public function setBadges($badges)
    {
        $this->badges = $badges;

        return $this;
    }

    /**
     * Get badges
     *
     * @return array
     */
    public function getBadges()
    {
        return $this->badges;
    }

	/**
	 * @param $badgeName
	 * @return bool
	 */
	public function hasBadge($badgeName)
	{
		return array_key_exists($badgeName, $this->badges);
	}

	/**
	 * @param Badge $badge
	 * @return $this
	 */
	public function addBadge(Badge $badge)
	{
		$this->badges[$badge->getName()] = $badge;

		return $this;
	}

	/**
	 * @param $badgeName
	 * @return Badge
	 */
	public function getBadge($badgeName)
	{
		if (! $this->hasBadge($badgeName)) {
			return false;
		}

		return $this->badges[$badgeName];
	}

	/**
	 * @param $badgeName
	 * @return $this
	 */
	public function removeBadge($badgeName)
	{
		if (($key = array_search($badgeName, $this->badges)) !== false) {
			unset($this->badges[$key]);
		}

		return $this;
	}

	/**
	 * Set options
	 *
	 * @param UserOptionsCollection $options
	 * @return User
	 */
	public function setOptions(UserOptionsCollection $options)
	{
		$this->options = $options->toArray();

		return $this;
	}

	/**
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	public function setOption($key, $value)
	{
		$this->options[$key] = $value;

		return $this;
	}

    /**
     * Get options
     *
     * @return UserOptionsCollection
     */
    public function getOptions()
    {
        return new UserOptionsCollection($this->options);
    }

	/**
	 * @param $key
	 * @return mixed
	 */
	public function getOption($key)
	{
		return $this->getOptions()->get($key);
	}

    /**
     * Set lastVisitHome
     *
     * @param \DateTime $lastVisitHome
     * @return User
     */
    public function setLastVisitHome($lastVisitHome)
    {
        $this->lastVisitHome = $lastVisitHome;

        return $this;
    }

    /**
     * Get lastVisitHome
     *
     * @return \DateTime
     */
    public function getLastVisitHome()
    {
        return $this->lastVisitHome;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return User
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
     * @return User
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
     * @return User
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

    /**
     * Add memberships
     *
     * @param \Etu\Core\UserBundle\Entity\Member $memberships
     * @return User
     */
    public function addMembership(\Etu\Core\UserBundle\Entity\Member $memberships)
    {
        $this->memberships[] = $memberships;

        return $this;
    }

    /**
     * Remove memberships
     *
     * @param \Etu\Core\UserBundle\Entity\Member $memberships
     */
    public function removeMembership(\Etu\Core\UserBundle\Entity\Member $memberships)
    {
        $this->memberships->removeElement($memberships);
    }

    /**
     * Get memberships
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMemberships()
    {
        return $this->memberships;
    }
}
