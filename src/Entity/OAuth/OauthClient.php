<?php

namespace Etu\Core\ApiBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\User;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * OauthClients.
 *
 * @ORM\Table(name="oauth_clients", indexes={ @ORM\Index(name="client_index", columns={ "clientId" }) })
 * @ORM\Entity
 */
class OauthClient
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=80)
     */
    private $clientId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=80, nullable=false)
     */
    private $clientSecret;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=80)
     */
    private $name;

    /**
     * Device UID for native applications.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $deviceUID;

    /**
     * Readeable device name that will be shown to user for native applications.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $device;

    /**
     * Expo Push Token, used to send notifications to mobile devices.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $pushToken;

    /**
     * If native client application. Only `client_credentials` mode is possible
     * and it will appear as "mobile application" on user profile and not in dev
     * pannel.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $native = false;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=2000, nullable=false)
     */
    private $redirectUri;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $deletedAt;

    /**
     * @var OauthScope[]
     *
     * @ORM\ManyToMany(targetEntity="OauthScope")
     * @ORM\JoinTable(name="oauth_clients_scopes")
     */
    private $scopes;

    /**
     * @var UploadedFile
     *
     * @Assert\Image(maxSize = "2M", minWidth = 150, minHeight = 150)
     */
    public $file;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->scopes = new ArrayCollection();
    }

    /**
     * Upload the photo.
     *
     * @return bool
     */
    public function upload()
    {
        $rootDir = __DIR__.'/../../../../../web/uploads/apps';
        $path = $rootDir.'/'.$this->getClientId().'.png';

        /*
         * Upload and resize
         */
        $imagine = new Imagine();

        // Create the logo thumbnail in a 200x200 box
        if (null === $this->file && !file_exists($path)) {
            $thumbnail = $imagine->open($rootDir.'/default.png')
                ->thumbnail(new Box(200, 200), Image::THUMBNAIL_OUTBOUND);
            $thumbnail->save($path);
        } elseif (null !== $this->file) {
            $thumbnail = $imagine->open($this->file->getPathname())
                ->thumbnail(new Box(200, 200), Image::THUMBNAIL_OUTBOUND);
            $thumbnail->save($path);
        }
    }

    /**
     * @return int
     */
    public function generateClientId()
    {
        return $this->clientId = random_int(100000000, 2100000000) * 25;
    }

    /**
     * @return string
     */
    public function generateClientSecret()
    {
        return $this->clientSecret = md5(uniqid(time(), true));
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
     * Get client icon.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->clientId.'.png';
    }

    /**
     * Set clientId.
     *
     * @param string $clientId
     *
     * @return OauthClient
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get clientId.
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set clientSecret.
     *
     * @param string $clientSecret
     *
     * @return OauthClient
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * Get clientSecret.
     *
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return OauthClient
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set deviceUID.
     *
     * @param string deviceUID
     * @param mixed $deviceUID
     *
     * @return OauthClient
     */
    public function setDeviceUID($deviceUID)
    {
        $this->deviceUID = $deviceUID;

        return $this;
    }

    /**
     * Get deviceUID.
     *
     * @return string
     */
    public function getDeviceUID()
    {
        return $this->deviceUID;
    }

    /**
     * Set device.
     *
     * @param string device
     * @param mixed $device
     *
     * @return OauthClient
     */
    public function setDevice($device)
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Get device.
     *
     * @return string
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Set push token.
     *
     * @param string pushToken
     * @param mixed $pushToken
     *
     * @return OauthClient
     */
    public function setPushToken($pushToken)
    {
        $this->pushToken = $pushToken;

        return $this;
    }

    /**
     * Get push token.
     *
     * @return string
     */
    public function getPushToken()
    {
        return $this->pushToken;
    }

    /**
     * Set if native.
     *
     * @param bool native
     * @param mixed $native
     *
     * @return OauthClient
     */
    public function setNative($native)
    {
        $this->native = $native;

        return $this;
    }

    /**
     * Is native.
     *
     * @return bool
     */
    public function getNative()
    {
        return $this->native;
    }

    /**
     * Set redirectUri.
     *
     * @param string $redirectUri
     *
     * @return OauthClient
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    /**
     * Get redirectUri.
     *
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * Set grantTypes.
     *
     * @param string $grantTypes
     *
     * @return OauthClient
     */
    public function setGrantTypes($grantTypes)
    {
        $this->grantTypes = $grantTypes;

        return $this;
    }

    /**
     * Get grantTypes.
     *
     * @return string
     */
    public function getGrantTypes()
    {
        return $this->grantTypes;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return OauthClient
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
     * Set user.
     *
     * @param User $user
     *
     * @return OauthClient
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add scopes.
     *
     * @return OauthClient
     */
    public function addScope(OauthScope $scopes)
    {
        $this->scopes[] = $scopes;

        return $this;
    }

    /**
     * Remove scopes.
     */
    public function removeScope(OauthScope $scopes)
    {
        $this->scopes->removeElement($scopes);
    }

    /**
     * Get scopes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getScopes()
    {
        return $this->scopes;
    }
}
