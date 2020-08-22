<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Collection\UserOptionsCollection;
use Etu\Core\UserBundle\Ldap\Model\User as LdapUser;
use Etu\Core\UserBundle\Model\BadgesManager;
use Etu\Core\UserBundle\Model\SemesterManager;
use Gedmo\Mapping\Annotation as Gedmo;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\Point;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User.
 *
 * @ORM\Table(
 *    name="etu_users",
 *    uniqueConstraints={@ORM\UniqueConstraint(name="search", columns={"login", "studentId"})},
 *    indexes={@ORM\Index(name="mail_index", columns={"mail"})}
 * )
 * @ORM\Entity()
 *
 */
class User implements UserInterface, EquatableInterface, \Serializable
{
    public const SEX_MALE = 'male';
    public const SEX_FEMALE = 'female';

    public const PRIVACY_PUBLIC = 100;
    public const PRIVACY_PRIVATE = 200;

    public static $branches = [
        'ING A2I' => 'A2I', 'ING GI' => 'GI',
        'ING GM' => 'GM', 'ING ISI' => 'ISI',
        'ING MTE' => 'MTE',  'ING MM' => 'MM',
        'ING RT' => 'RT', 'ING TC' => 'TC',
        'MST ISC' => 'ISC', 'MST PAIP' => 'PAIP',
        'MST RE' => 'RE', 'LP ETN' => 'LP-ETN',
        'LP MEER' => 'LP-MEER', 'LP MPHP' => 'LP-MPHP',
        'DOC EXT' => 'EXT', 'DOC M2ON' => 'M2ON',
        'DOC OSS' => 'OSS', 'DOC SST' => 'SST',
        'MS MPTI' => 'MS-MPTI', 'MS EBAM' => 'MS-EBAM',
        'MS EFC' => 'MS-EFC', 'MS EST' => 'MS-EST',
        'CV ING' => ' CV ING', 'DU DPO' => 'DU-DPO',
        'DU ARMC' => 'DU-ARMC', 'DU 3C' => 'DU-3C',
        'DU DE' => 'DU-DE', 'DU IOBM' => 'DU-IOBM',
        'FC' => 'FC',
    ];

    public static $levels = [
        '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5',
        '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10',
    ];

    public static $ldap = [
        "Encore à l'UTT" => true, "N'est plus à l'UTT" => false,
    ];

    public static $filieres = [
        'Aucune' => 'Aucune', 'Libre' => 'Libre', 'ING A2I SPI' => 'SPI',
        'ING A2I TEI' => 'TEI', 'ING GI LET' => 'LET',
        'ING GI LIP' => 'LIP', 'ING GI RAMS' => 'RAMS',
        'ING GI SFERE (*)' => 'SFERE', 'ING GM CEISME' => 'CEISME',
        'ING GM MDPI' => 'MDPI', 'ING GM SNM' => 'SNM',
        'ING GM TIM (*)' => 'TIM', 'ING ISI ATN' => 'ATN',
        'ING ISI IPL' => 'IPL', 'ING ISI VDC' => 'VDC',
        'ING ISI MPL (*)' => 'MPL', 'ING ISI MRI (*)' => 'MRI',
        'ING ISI MSI (*)' => 'MSI', 'ING MTE EME' => 'EME',
        'ING MTE TCMC' => 'TCMC', 'ING MTE TQM' => 'TQM',
        'ING RT CSR' => 'CSR', 'ING RT SSC' => 'SSC',
        'ING RT TMOC' => 'TMOC', 'MST ISC OSS' => 'OSS',
        'MST ISC SSI' => 'SSI', 'MST PAIP MMPA' => 'MMPA',
        'MST PAIP ONT' => 'ONT', 'MST RE IMEDD' => 'IMEDD',
        'MST RE IMSGA' => 'IMSGA',
    ];

    /**
     * @var int
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
     * Password for who are not in CAS.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $password;

    /**
     * @var int
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
     * @Assert\Length(
     *      min = "2", max = "50",
     *      minMessage = "user.validation.firstname.length_min",
     *      maxMessage = "user.validation.firstname.length_max"
     * )
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(
     *      min = "2", max = "50",
     *      minMessage = "user.validation.lastname.length_min",
     *      maxMessage = "user.validation.lastname.length_max"
     * )
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
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $branch;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, nullable=true)
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
     * @Assert\Regex(
     *      pattern = "/^0[1-9]([-. ]?[0-9]{2}){4}$/",
     *      message = "user.validation.phoneNumber"
     * )
     */
    protected $phoneNumber;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $phoneNumberPrivacy;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
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
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $sexPrivacy;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Assert\Length(
     *      min = "2", max = "50",
     *      minMessage = "user.validation.nationality.length_min",
     *      minMessage = "user.validation.nationality.length_max"
     * )
     */
    protected $nationality;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $nationalityPrivacy;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\Length(
     *      min = "2", max = "100",
     *      minMessage = "user.validation.address.length_min",
     *      minMessage = "user.validation.address.length_max"
     * )
     */
    protected $address;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $addressPrivacy;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $postalCode;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $postalCodePrivacy;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\Length(
     *      min = "2", max = "50",
     *      minMessage = "user.validation.city.length_min",
     *      minMessage = "user.validation.city.length_max"
     * )
     */
    protected $city;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $cityPrivacy;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Assert\Length(
     *      min = "2", max = "50",
     *      minMessage = "user.validation.country.length_min",
     *      minMessage = "user.validation.country.length_max"
     * )
     */
    protected $country;

    /**
     * @var int
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
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $birthdayPrivacy;

    /**
     * @var bool
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
     * @var int
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
     * @var string
     *             > For trombi
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Assert\Length(
     *      max = "50",
     *      maxMessage = "user.validation.surnom.length_max"
     * )
     */
    protected $surnom;

    /**
     * @var string
     *             > For trombi
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $jadis;

    /**
     * @var string
     *             > For trombi
     *
     * @ORM\Column(type="text", nullable=true)
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
     * Is or was a student at the UTT.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $isStudent;

    /**
     * Is or was a member of the staff of the UTT.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $isStaffUTT;

    /**
     * Is true if in the last synchronization with LDAP, user was in LDAP.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $isInLDAP;

    /**
     * Keeps its account even if not in LDAP anymore
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $isKeepingAccount;

    /**
     * Read-only expiration date.
     *
     * @var mixed
     *
     * @ORM\Column(type="datetime", nullable = true))
     */
    protected $readOnlyExpirationDate;

    /**
     * Banned expiration date.
     *
     * @var mixed
     *
     * @ORM\Column(type="datetime", nullable = true))
     */
    protected $bannedExpirationDate;

    /**
     * Roles which are given especially to that user.
     * Roles generated like ROLE_USER or ROLE_STUDENT are not stored here.
     *
     * @var array
     *
     * @ORM\Column(type="array")
     */
    protected $storedRoles;

    /**
     * @var UserBadge[]
     *
     * @ORM\OneToMany(targetEntity="UserBadge", mappedBy="user")
     */
    protected $badges;

    /**
     * Modules options (no format, just an array stored to be sued by modules as they want).
     *
     * @var array
     *
     * @ORM\Column(type="array")
     */
    protected $options;

    /**
     * Semesters history for badges and customizations.
     *
     * @var array
     *
     * @ORM\Column(type="array")
     */
    protected $semestersHistory = [];

    /**
     * Last visit date.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $lastVisitHome;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $bdeMembershipStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $bdeMembershipEnd;

    /**
     * @var bool Subscribed to daymail
     *
     * @ORM\Column(type="boolean")
     */
    protected $daymail;

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
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $deletedAt;

    /**
     * @var \Doctrine\Common\Collections\Collection<Member>
     *
     * @ORM\OneToMany(targetEntity="\Etu\Core\UserBundle\Entity\Member", mappedBy="user")
     * @ORM\JoinColumn()
     */
    protected $memberships;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $firstLogin = false;

    /**
     * Temporary variable to store uploaded file during photo update.
     *
     * @var UploadedFile
     *
     * @Assert\Image(maxSize = "4M", minWidth = 100, minHeight = 100)
     */
    public $file;

    /**
     * Is testing context ?
     *
     * @var bool
     */
    public $testingContext;

    /**
     * @var string
     * @ORM\Column(type = "uuid")
     */
    protected $privateToken;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $schedulePrivacy;

    /*
     * Methods
     */

    public function __construct()
    {
        $this->testingContext = false;
        $this->isStudent = false;
        $this->isStaffUTT = false;
        $this->isInLDAP = false;
        $this->readOnlyExpirationDate = null;
        $this->bannedExpirationDate = null;
        $this->storedRoles = [];
        $this->avatar = 'default-avatar.png';
        $this->phoneNumberPrivacy = self::PRIVACY_PUBLIC;
        $this->sexPrivacy = self::PRIVACY_PUBLIC;
        $this->nationalityPrivacy = self::PRIVACY_PUBLIC;
        $this->addressPrivacy = self::PRIVACY_PUBLIC;
        $this->postalCodePrivacy = self::PRIVACY_PUBLIC;
        $this->cityPrivacy = self::PRIVACY_PUBLIC;
        $this->countryPrivacy = self::PRIVACY_PUBLIC;
        $this->birthdayPrivacy = self::PRIVACY_PUBLIC;
        $this->schedulePrivacy = self::PRIVACY_PUBLIC;
        $this->isKeepingAccount = false;
        $this->birthdayDisplayOnlyAge = false;
        $this->personnalMailPrivacy = self::PRIVACY_PUBLIC;
        $this->options = new UserOptionsCollection();
        $this->badges = new ArrayCollection();
        $this->permissions = [];
        $this->ldapInformations = new LdapUser();
        $this->uvs = '';
        $this->lastVisitHome = new \DateTime();
        $this->createdAt = new \DateTime();
        $this->firstLogin = false;
        $this->generatePrivateToken();
    }

    public function __toString()
    {
        return $this->fullName;
    }

    /**
     * Return available branches and levels for forms.
     *
     * @return array
     */
    public static function availableBranches()
    {
        $result = [];

        foreach (self::$branches as $branch) {
            foreach (self::$levels as $level) {
                $result[] = $branch.$level;
            }
        }

        return $result;
    }

    /**
     * Upload the photo.
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

        // Save the result
        $image->save(__DIR__.'/../../../../../web/uploads/photos/'.$this->getLogin().'.png');

        $this->avatar = $this->getLogin().'.png';

        return $this->avatar;
    }

    /**
     * @return bool
     */
    public function getIsOrga()
    {
        return false;
    }

    /**
     * @return int
     */
    public function getProfileCompletion()
    {
        $infos = [
            $this->phoneNumber, $this->sex, $this->nationality, $this->address, $this->postalCode, $this->city,
            $this->country, $this->birthday, $this->personnalMail,
        ];

        $completion = 0;
        $count = 0;

        foreach ($infos as $value) {
            ++$count;

            if (!empty($value)) {
                ++$completion;
            }
        }

        return round($completion / $count, 2) * 100;
    }

    /**
     * @return int
     */
    public function getTrombiCompletion()
    {
        $infos = [$this->surnom, $this->jadis, $this->passions];

        $completion = 0;
        $count = 0;

        foreach ($infos as $value) {
            ++$count;

            if (!empty($value)) {
                ++$completion;
            }
        }

        return round($completion / $count, 2) * 100;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->login;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return md5($this->login.$this->mail);
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set login.
     *
     * @param string $login
     *
     * @return User
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login.
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param int $studentId
     *
     * @return User
     */
    public function setStudentId($studentId)
    {
        if ('NC' == $studentId) {
            $studentId = null;
        }

        $this->studentId = $studentId;

        return $this;
    }

    /**
     * Get studentId.
     *
     * @return int
     */
    public function getStudentId()
    {
        return $this->studentId;
    }

    /**
     * Set mail.
     *
     * @param string $mail
     *
     * @return User
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail.
     *
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param string $fullName
     *
     * @return User
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        $parts = explode(' ', $fullName);

        $this->firstName = $parts[0];
        unset($parts[0]);

        if (!empty($parts)) {
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
     * Set firstName.
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName.
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $formation
     *
     * @return User
     */
    public function setFormation($formation)
    {
        if ('nc' == mb_strtolower($formation)) {
            $formation = null;
        }

        $this->formation = $formation;

        return $this;
    }

    /**
     * Get formation.
     *
     * @return string
     */
    public function getFormation()
    {
        return $this->formation;
    }

    /**
     * @param string $branch
     *
     * @return $this
     */
    public function setBranch($branch)
    {
        if ('NC' == $branch) {
            $branch = null;
        }

        $this->branch = $branch;

        return $this;
    }

    /**
     * @return string
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @param string $niveau
     *
     * @return User
     */
    public function setNiveau($niveau)
    {
        if ('NC' == $niveau) {
            $niveau = null;
        }

        $this->niveau = $niveau;

        return $this;
    }

    /**
     * Get niveau.
     *
     * @return string
     */
    public function getNiveau()
    {
        return $this->niveau;
    }

    /**
     * @param string $filiere
     *
     * @return User
     */
    public function setFiliere($filiere)
    {
        if ('NC' == $filiere) {
            $filiere = null;
        }

        $this->filiere = $filiere;

        return $this;
    }

    /**
     * Get filiere.
     *
     * @return string
     */
    public function getFiliere()
    {
        return $this->filiere;
    }

    /**
     * @param string $phoneNumber
     *
     * @return User
     */
    public function setPhoneNumber($phoneNumber)
    {
        if ('NC' == $phoneNumber) {
            $phoneNumber = null;
        }

        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber.
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set phoneNumberPrivacy.
     *
     * @param int $phoneNumberPrivacy
     *
     * @return User
     */
    public function setPhoneNumberPrivacy($phoneNumberPrivacy)
    {
        $this->phoneNumberPrivacy = $phoneNumberPrivacy;

        return $this;
    }

    /**
     * Get phoneNumberPrivacy.
     *
     * @return int
     */
    public function getPhoneNumberPrivacy()
    {
        return $this->phoneNumberPrivacy;
    }

    /**
     * @param string $title
     *
     * @return User
     */
    public function setTitle($title)
    {
        if ('NC' == $title) {
            $title = null;
        }

        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $room
     *
     * @return User
     */
    public function setRoom($room)
    {
        if ('NC' == $room) {
            $room = null;
        }

        $this->room = $room;

        return $this;
    }

    /**
     * Get room.
     *
     * @return string
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * Set avatar.
     *
     * @param string $avatar
     *
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar.
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set sex.
     *
     * @param string $sex
     *
     * @return User
     */
    public function setSex($sex)
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * Get sex.
     *
     * @return string
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Set sexPrivacy.
     *
     * @param int $sexPrivacy
     *
     * @return User
     */
    public function setSexPrivacy($sexPrivacy)
    {
        $this->sexPrivacy = $sexPrivacy;

        return $this;
    }

    /**
     * Get sexPrivacy.
     *
     * @return int
     */
    public function getSexPrivacy()
    {
        return $this->sexPrivacy;
    }

    /**
     * Set nationality.
     *
     * @param string $nationality
     *
     * @return User
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;

        return $this;
    }

    /**
     * Get nationality.
     *
     * @return string
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * Set nationalityPrivacy.
     *
     * @param int $nationalityPrivacy
     *
     * @return User
     */
    public function setNationalityPrivacy($nationalityPrivacy)
    {
        $this->nationalityPrivacy = $nationalityPrivacy;

        return $this;
    }

    /**
     * Get nationalityPrivacy.
     *
     * @return int
     */
    public function getNationalityPrivacy()
    {
        return $this->nationalityPrivacy;
    }

    /**
     * Set address.
     *
     * @param string $address
     *
     * @return User
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set addressPrivacy.
     *
     * @param int $addressPrivacy
     *
     * @return User
     */
    public function setAddressPrivacy($addressPrivacy)
    {
        $this->addressPrivacy = $addressPrivacy;

        return $this;
    }

    /**
     * Get addressPrivacy.
     *
     * @return int
     */
    public function getAddressPrivacy()
    {
        return $this->addressPrivacy;
    }

    /**
     * Set postalCode.
     *
     * @param string $postalCode
     *
     * @return User
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode.
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set postalCodePrivacy.
     *
     * @param int $postalCodePrivacy
     *
     * @return User
     */
    public function setPostalCodePrivacy($postalCodePrivacy)
    {
        $this->postalCodePrivacy = $postalCodePrivacy;

        return $this;
    }

    /**
     * Get postalCodePrivacy.
     *
     * @return int
     */
    public function getPostalCodePrivacy()
    {
        return $this->postalCodePrivacy;
    }

    /**
     * Set city.
     *
     * @param string $city
     *
     * @return User
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set cityPrivacy.
     *
     * @param int $cityPrivacy
     *
     * @return User
     */
    public function setCityPrivacy($cityPrivacy)
    {
        $this->cityPrivacy = $cityPrivacy;

        return $this;
    }

    /**
     * Get cityPrivacy.
     *
     * @return int
     */
    public function getCityPrivacy()
    {
        return $this->cityPrivacy;
    }

    /**
     * Set country.
     *
     * @param string $country
     *
     * @return User
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set countryPrivacy.
     *
     * @param int $countryPrivacy
     *
     * @return User
     */
    public function setCountryPrivacy($countryPrivacy)
    {
        $this->countryPrivacy = $countryPrivacy;

        return $this;
    }

    /**
     * Get countryPrivacy.
     *
     * @return int
     */
    public function getCountryPrivacy()
    {
        return $this->countryPrivacy;
    }

    /**
     * Set birthday.
     *
     * @param \DateTime $birthday
     *
     * @return User
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday.
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @return int
     */
    public function getAge()
    {
        return $this->birthday->diff(new \DateTime())->y;
    }

    /**
     * Set birthdayPrivacy.
     *
     * @param int $birthdayPrivacy
     *
     * @return User
     */
    public function setBirthdayPrivacy($birthdayPrivacy)
    {
        $this->birthdayPrivacy = $birthdayPrivacy;

        return $this;
    }

    /**
     * Get birthdayPrivacy.
     *
     * @return int
     */
    public function getBirthdayPrivacy()
    {
        return $this->birthdayPrivacy;
    }

    /**
     * Set birthdayDisplayOnlyAge.
     *
     * @param bool $birthdayDisplayOnlyAge
     *
     * @return User
     */
    public function setBirthdayDisplayOnlyAge($birthdayDisplayOnlyAge)
    {
        $this->birthdayDisplayOnlyAge = $birthdayDisplayOnlyAge;

        return $this;
    }

    /**
     * Get birthdayDisplayOnlyAge.
     *
     * @return bool
     */
    public function getBirthdayDisplayOnlyAge()
    {
        return $this->birthdayDisplayOnlyAge;
    }

    /**
     * Set personnalMail.
     *
     * @param string $personnalMail
     *
     * @return User
     */
    public function setPersonnalMail($personnalMail)
    {
        $this->personnalMail = $personnalMail;

        return $this;
    }

    /**
     * Get personnalMail.
     *
     * @return string
     */
    public function getPersonnalMail()
    {
        return $this->personnalMail;
    }

    /**
     * Set personnalMailPrivacy.
     *
     * @param int $personnalMailPrivacy
     *
     * @return User
     */
    public function setPersonnalMailPrivacy($personnalMailPrivacy)
    {
        $this->personnalMailPrivacy = $personnalMailPrivacy;

        return $this;
    }

    /**
     * Get personnalMailPrivacy.
     *
     * @return int
     */
    public function getPersonnalMailPrivacy()
    {
        return $this->personnalMailPrivacy;
    }

    /**
     * Set language.
     *
     * @param string $language
     *
     * @return User
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set surnom.
     *
     * @param string $surnom
     *
     * @return User
     */
    public function setSurnom($surnom)
    {
        $this->surnom = $surnom;

        return $this;
    }

    /**
     * Get surnom.
     *
     * @return string
     */
    public function getSurnom()
    {
        return $this->surnom;
    }

    /**
     * Set jadis.
     *
     * @param string $jadis
     *
     * @return User
     */
    public function setJadis($jadis)
    {
        $this->jadis = $jadis;

        return $this;
    }

    /**
     * Get jadis.
     *
     * @return string
     */
    public function getJadis()
    {
        return $this->jadis;
    }

    /**
     * Set passions.
     *
     * @param string $passions
     *
     * @return User
     */
    public function setPassions($passions)
    {
        $this->passions = $passions;

        return $this;
    }

    /**
     * Get passions.
     *
     * @return string
     */
    public function getPassions()
    {
        return $this->passions;
    }

    /**
     * Set website.
     *
     * @param string $website
     *
     * @return User
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website.
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set facebook.
     *
     * @param string $facebook
     *
     * @return User
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * Get facebook.
     *
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set twitter.
     *
     * @param string $twitter
     *
     * @return User
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;

        return $this;
    }

    /**
     * Get twitter.
     *
     * @return string
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * Set linkedin.
     *
     * @param string $linkedin
     *
     * @return User
     */
    public function setLinkedin($linkedin)
    {
        $this->linkedin = $linkedin;

        return $this;
    }

    /**
     * Get linkedin.
     *
     * @return string
     */
    public function getLinkedin()
    {
        return $this->linkedin;
    }

    /**
     * Set viadeo.
     *
     * @param string $viadeo
     *
     * @return User
     */
    public function setViadeo($viadeo)
    {
        $this->viadeo = $viadeo;

        return $this;
    }

    /**
     * Get viadeo.
     *
     * @return string
     */
    public function getViadeo()
    {
        return $this->viadeo;
    }

    /**
     * Set uvs.
     *
     * @param string $uvs
     *
     * @return User
     */
    public function setUvs($uvs)
    {
        $this->uvs = $uvs;

        return $this;
    }

    /**
     * Get uvs.
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
     * Retrieves the currently set isInLDAP.
     */
    public function getIsInLDAP()
    {
        return $this->isInLDAP;
    }

    /**
     * Sets the isInLDAP to use.
     *
     * @param mixed $isInLDAP
     *
     * @return $this
     */
    public function setIsInLDAP($isInLDAP): self
    {
        $this->isInLDAP = $isInLDAP;

        return $this;
    }

    /**
     * Retrieves the currently set isStudent.
     */
    public function getIsStudent(): bool
    {
        return $this->isStudent;
    }

    /**
     * Sets the isStudent to use.
     *
     * @return $this
     */
    public function setIsStudent(bool $isStudent): self
    {
        $this->isStudent = $isStudent;

        return $this;
    }

    /**
     * Retrieves the currently set isStaffUTT.
     */
    public function getIsStaffUTT(): bool
    {
        return $this->isStaffUTT;
    }

    /**
     * Sets the isStaffUTT to use.
     *
     * @return $this
     */
    public function setIsStaffUTT(bool $isStaffUTT): self
    {
        $this->isStaffUTT = $isStaffUTT;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsKeepingAccount() : bool
    {
        return $this->isKeepingAccount;
    }

    /**
     * @param bool $isKeepingAccount
     * @return User
     */
    public function setIsKeepingAccount(bool $isKeepingAccount) : self
    {
        $this->isKeepingAccount = $isKeepingAccount;
        return $this;
    }

    /**
     * Retrieves the currently set storedRoles.
     */
    public function getStoredRoles(): array
    {
        return $this->storedRoles;
    }

    /**
     * Sets the storedRoles to use.
     *
     * @return $this
     */
    public function setStoredRoles(array $storedRoles): self
    {
        $this->storedRoles = $storedRoles;

        return $this;
    }

    /**
     * Retrieves the currently set bannedExpirationDate.
     *
     * @return mixed
     */
    public function getBannedExpirationDate()
    {
        return $this->bannedExpirationDate;
    }

    /**
     * Sets the bannedExpirationDate to use.
     *
     * @param mixed $bannedExpirationDate
     *
     * @return $this
     */
    public function setBannedExpirationDate($bannedExpirationDate): self
    {
        $this->bannedExpirationDate = $bannedExpirationDate;

        return $this;
    }

    /**
     * Set readOnlyExpirationDate.
     *
     * @param \DateTime $readOnlyExpirationDate
     *
     * @return User
     */
    public function setReadOnlyExpirationDate($readOnlyExpirationDate)
    {
        $this->readOnlyExpirationDate = $readOnlyExpirationDate;

        return $this;
    }

    /**
     * Get readOnlyExpirationDate.
     *
     * @return \DateTime
     */
    public function getReadOnlyExpirationDate()
    {
        return $this->readOnlyExpirationDate;
    }

    /**
     * Set daymail.
     *
     * @param bool daymail
     * @param mixed $daymail
     *
     * @return User
     */
    public function setDaymail($daymail)
    {
        $this->daymail = $daymail;

        return $this;
    }

    /**
     * Get daymail.
     *
     * @return bool
     */
    public function getDaymail()
    {
        return $this->daymail;
    }

    /**
     * Set options.
     *
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
     *
     * @return $this
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Get options.
     *
     * @return UserOptionsCollection
     */
    public function getOptions()
    {
        return new UserOptionsCollection($this->options);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getOption($key)
    {
        return $this->getOptions()->get($key);
    }

    /**
     * Set lastVisitHome.
     *
     * @param \DateTime $lastVisitHome
     *
     * @return User
     */
    public function setLastVisitHome($lastVisitHome)
    {
        $this->lastVisitHome = $lastVisitHome;

        return $this;
    }

    /**
     * Get lastVisitHome.
     *
     * @return \DateTime
     */
    public function getLastVisitHome()
    {
        return $this->lastVisitHome;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return User
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
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return User
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set deletedAt.
     *
     * @param \DateTime $deletedAt
     *
     * @return User
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
     * Add memberships.
     *
     * @return User
     */
    public function addMembership(Member $memberships)
    {
        $this->memberships[] = $memberships;

        return $this;
    }

    /**
     * Remove memberships.
     */
    public function removeMembership(Member $memberships)
    {
        $this->memberships->removeElement($memberships);
    }

    /**
     * Get memberships.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMemberships()
    {
        return $this->memberships;
    }

    /**
     * @return array
     */
    public function addCureentSemesterToHistory()
    {
        $history = [
            'formation' => $this->formation,
            'branch' => $this->branch,
            'niveau' => $this->niveau,
            'filiere' => $this->filiere,
            'uvs' => $this->getUvsList(),
        ];

        $this->semestersHistory[SemesterManager::current()->getCode()] = $history;

        return $history;
    }

    /**
     * @return string
     */
    public static function currentSemester()
    {
        return SemesterManager::current()->getCode();
    }

    /**
     * Set semestersHistory.
     *
     * @param array $semestersHistory
     *
     * @return User
     */
    public function setSemestersHistory($semestersHistory)
    {
        $this->semestersHistory = $semestersHistory;

        return $this;
    }

    /**
     * Get semestersHistory.
     *
     * @return array
     */
    public function getSemestersHistory()
    {
        return $this->semestersHistory;
    }

    /**
     * Add badges.
     *
     * @return User
     */
    public function addBadge(UserBadge $badges)
    {
        $this->badges[] = $badges;

        return $this;
    }

    /**
     * Remove badges.
     */
    public function removeBadge(UserBadge $badges)
    {
        $this->badges->removeElement($badges);
    }

    /**
     * @return ArrayCollection|UserBadge[]
     */
    public function getBadges()
    {
        return $this->badges;
    }

    /**
     * Get badges (generate some of them on the fly).
     *
     * @return array
     */
    public function getBadgesList()
    {
        if ($this->testingContext) {
            return [];
        }

        $badges = ($this->badges) ? $this->badges->toArray() : [];

        foreach ($badges as &$badge) {
            $badge = $badge->getBadge();
        }

        $count = \count($badges);

        if ('TC' == $this->getBranch() && '1' == $this->getNiveau()) {
            BadgesManager::userAddBadge($this, 'tc01');
            ++$count;
        }

        if ('TC' == $this->getBranch() && '6' == $this->getNiveau()) {
            BadgesManager::userAddBadge($this, 'tc06');
            ++$count;
        }

        if (BadgesManager::userHasBadge($this, 'subscriber')
                && BadgesManager::userHasBadge($this, 'forum_message')
                && BadgesManager::userHasBadge($this, 'profile_completed')) {
            BadgesManager::userAddBadge($this, 'starter');
            ++$count;
        }

        /** @var Member[] $memberships */
        $memberships = ($this->getMemberships()) ? $this->getMemberships()->toArray() : [];

        if (\count($memberships) > 0) {
            BadgesManager::userAddBadge($this, 'orga_member', 1);
            ++$count;
        }

        foreach ($memberships as $member) {
            if ($member->isFromBureau()) {
                BadgesManager::userAddBadge($this, 'orga_member', 2);
                ++$count;
            }

            if (Member::ROLE_PRESIDENT == $member->getRole()) {
                BadgesManager::userAddBadge($this, 'orga_member', 3);
                ++$count;

                if ('bde' == $member->getOrganization()->getLogin()) {
                    BadgesManager::userAddBadge($this, 'orga_member', 4);
                    ++$count;
                }
            }
        }

        if ($count >= 10) {
            BadgesManager::userAddBadge($this, 'challenge', 1);
        }
        if ($count >= 20) {
            BadgesManager::userAddBadge($this, 'challenge', 2);
        }
        if ($count >= 30) {
            BadgesManager::userAddBadge($this, 'challenge', 3);
        }
        if ($count >= 40) {
            BadgesManager::userAddBadge($this, 'challenge', 4);
        }
        if ($count >= 50) {
            BadgesManager::userAddBadge($this, 'challenge', 5);
        }
        if ($count >= 60) {
            BadgesManager::userAddBadge($this, 'challenge', 6);
        }

        /*
         * Create a usable list
         */
        $list = [];

        $all_badges = (array) BadgesManager::findBadgesList();
        foreach ($all_badges as $serie => $badges) {
            foreach ((array) $badges as $level => $badge) {
                if (\count($badges) > 1) {
                    $list[$serie][$level] = [
                        'owned' => false,
                        'badge' => $badge,
                    ];
                } else {
                    $list['singles'][$serie] = [
                        'owned' => false,
                        'badge' => $badge,
                    ];
                }
            }
        }

        $userBadges = ($this->badges) ? $this->badges->toArray() : [];

        /** @var $userBadge UserBadge */
        foreach ($userBadges as $userBadge) {
            $badge = $userBadge->getBadge();
            $serieBadges = $all_badges[$badge->getSerie()];

            if (\count($serieBadges) > 1) {
                $list[$badge->getSerie()][$badge->getLevel()]['owned'] = true;
                $list[$badge->getSerie()][$badge->getLevel()]['createdAt'] = $userBadge->getCreatedAt();
            } else {
                $list['singles'][$badge->getSerie()]['owned'] = true;
                $list['singles'][$badge->getSerie()]['createdAt'] = $userBadge->getCreatedAt();
            }
        }

        return $list;
    }

    /**
     * Get badges 2D (generate some of them on the fly).
     *
     * @return array
     */
    public function getBadges2d()
    {
        $badges = [];

        foreach ($this->getBadgesList() as $serie) {
            foreach ($serie as $badge) {
                $badges[] = $badge;
            }
        }

        return $badges;
    }

    /**
     * Get badges 2D (generate some of them on the fly).
     *
     * @param mixed $count
     *
     * @return array
     */
    public function getLastBadges($count = 6)
    {
        $badges = [];
        $dates = [];
        $i = 0;

        /** @var $userBadge UserBadge */
        foreach ($this->badges->toArray() as $userBadge) {
            $badges[] = $userBadge->getBadge();
            $dates[] = $userBadge->getCreatedAt();

            ++$i;

            if ($i >= $count) {
                break;
            }
        }

        array_multisort($badges, $dates, SORT_DESC);

        return $badges;
    }

    /**
     * Set firstLogin.
     *
     * @param bool $firstLogin
     *
     * @return User
     */
    public function setFirstLogin($firstLogin)
    {
        $this->firstLogin = $firstLogin;

        return $this;
    }

    /**
     * Get firstLogin.
     *
     * @return bool
     */
    public function getFirstLogin()
    {
        return $this->firstLogin;
    }

    /**
     * @param \DateTime
     * @param mixed $bdeMembershipEnd
     *
     * @return $this
     */
    public function setBdeMembershipEnd($bdeMembershipEnd)
    {
        $this->bdeMembershipEnd = $bdeMembershipEnd;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBdeMembershipEnd()
    {
        return $this->bdeMembershipEnd;
    }

    /**
     * @return bool
     */
    public function isBdeMember()
    {
        return $this->bdeMembershipEnd
            && $this->bdeMembershipEnd > new \DateTime();
    }

    /**
     * Tell if user is currently in readonly mode.
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return null !== $this->getReadOnlyExpirationDate()
            && $this->getReadOnlyExpirationDate() instanceof \DateTime
            && $this->getReadOnlyExpirationDate() > new \DateTime();
    }

    /**
     * Tell if user is currently banned.
     *
     * @return bool
     */
    public function isBanned()
    {
        return null !== $this->getBannedExpirationDate()
            && $this->getBannedExpirationDate() instanceof \DateTime
            && $this->getBannedExpirationDate() > new \DateTime();
    }

    /**
     * Store a list of role in database for this user.
     *
     * @param array $roles An array of role
     */
    public function storeRoles(array $roles)
    {
        $this->storedRoles = array_merge($this->storedRoles, $roles);
    }

    /**
     * Remove a list of role from database for this user.
     *
     * @param array $roles An array of role
     */
    public function removeRoles(array $roles)
    {
        $this->storedRoles = array_diff($this->storedRoles, $roles);
    }

    /**
     * Store a role in database for this user.
     *
     * @param string $role A role
     */
    public function storeRole(string $role)
    {
        $this->storeRoles([$role]);
    }

    /**
     * Remove a role from database for this user.
     *
     * @param string $roles A role
     */
    public function removeRole(string $role)
    {
        $this->removeRoles([$role]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $roles = ['ROLE_USER'];

        if (!empty($this->password)) {
            $roles[] = 'ROLE_EXTERNAL';
        }
        if ($this->isInLDAP) {
            $roles[] = 'ROLE_CAS';
        }

        if ($this->isBanned()) {
            $roles[] = 'ROLE_BANNED';

            return $roles;
        }
        if ($this->isReadOnly()) {
            $roles[] = 'ROLE_READONLY';

            return $roles;
        }

        // Get roles from database
        $roles = array_merge($this->storedRoles, $roles);

        if ($this->isStaffUTT) {
            $roles[] = 'ROLE_STAFFUTT';
        }
        if ($this->isStudent) {
            $roles[] = 'ROLE_STUDENT';
        }

        return $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        //Serialize only attributs used in equals method
        return serialize([
            $this->id,
            $this->login,
            $this->password,
            $this->isStudent,
            $this->isStaffUTT,
            $this->isInLDAP,
            $this->readOnlyExpirationDate,
            $this->bannedExpirationDate,
            $this->storedRoles,
        ]);
    }

    /**
     * @see \Serializable::unserialize()
     *
     * @param mixed $serialized
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->login,
            $this->password,
            $this->isStudent,
            $this->isStaffUTT,
            $this->isInLDAP,
            $this->readOnlyExpirationDate,
            $this->bannedExpirationDate,
            $this->storedRoles
        ) = unserialize($serialized);
    }

    /**
     * The equality comparison is used to know when the token should be renewed.
     * So check only for equality of attributs that could change roles.
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        return ($user instanceof self)
            && $this->getLogin() === $user->getLogin()
            && $this->getPassword() === $user->getPassword()
            && $this->getIsStudent() === $user->getIsStudent()
            && $this->getIsStaffUTT() === $user->getIsStaffUTT()
            && $this->getIsInLDAP() === $user->getIsInLDAP()
            && $this->getReadOnlyExpirationDate() == $user->getReadOnlyExpirationDate()
            && $this->getBannedExpirationDate() == $user->getBannedExpirationDate()
            && $this->getStoredRoles() == $user->getStoredRoles();
    }

    /**
     * Set bdeMembershipStart.
     *
     * @param \DateTime|null $bdeMembershipStart
     *
     * @return User
     */
    public function setBdeMembershipStart($bdeMembershipStart = null)
    {
        $this->bdeMembershipStart = $bdeMembershipStart;

        return $this;
    }

    /**
     * Get bdeMembershipStart.
     *
     * @return \DateTime|null
     */
    public function getBdeMembershipStart()
    {
        return $this->bdeMembershipStart;
    }

    /**
     * Set privateToken.
     *
     * @param string|null $privateToken
     *
     * @return User
     */
    public function setPrivateToken($privateToken = null)
    {
        $this->privateToken = $privateToken;

        return $this;
    }

    /**
     * Get privateToken.
     *
     * @return string|null
     */
    public function getPrivateToken()
    {
        return $this->privateToken;
    }

    public function generatePrivateToken()
    {
        $this->privateToken = Uuid::getFactory()->uuid4();

        return $this;
    }

    public function getSchedulePrivacy(): int
    {
        return $this->schedulePrivacy;
    }

    public function setSchedulePrivacy(int $schedulePrivacy)
    {
        $this->schedulePrivacy = $schedulePrivacy;

        return $this;
    }
}
