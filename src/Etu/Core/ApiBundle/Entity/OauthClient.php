<?php

namespace Etu\Core\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\User;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * OauthClients
 *
 * @ORM\Table(name="oauth_clients", indexes={ @ORM\Index(name="client_index", columns={ "clientId" }) })
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class OauthClient
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var User $user
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
     * @ORM\JoinColumn()
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
     * @var \DateTime $deletedAt
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $deletedAt;

    /**
     * @var OauthScope[] $scopes
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
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->scopes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Upload the photo
     *
     * @return boolean
     */
    public function upload()
    {
        $rootDir = __DIR__ . '/../../../../../web/uploads/apps';

        /*
         * Upload and resize
         */
        $imagine = new Imagine();

        // Create the logo thumbnail in a 200x200 box
        if (null === $this->file) {
            $thumbnail = $imagine->open($rootDir . '/default.png')
                ->thumbnail(new Box(200, 200), Image::THUMBNAIL_OUTBOUND);
        } else {
            $thumbnail = $imagine->open($this->file->getPathname())
                ->thumbnail(new Box(200, 200), Image::THUMBNAIL_OUTBOUND);
        }

        // Save the result
        $thumbnail->save($rootDir . '/' . $this->getClientId().'.png');
    }

    /**
     * @return int
     */
    public function generateClientId()
    {
        return $this->clientId = mt_rand(100000000, 2100000000) * 25;
    }

    /**
     * @return string
     */
    public function generateClientSecret()
    {
        return $this->clientSecret = md5(uniqid(time(), true));
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
     * Get client icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->clientId . '.png';
    }

    /**
     * Set clientId
     *
     * @param string $clientId
     * @return OauthClient
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    
        return $this;
    }

    /**
     * Get clientId
     *
     * @return string 
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set clientSecret
     *
     * @param string $clientSecret
     * @return OauthClient
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    
        return $this;
    }

    /**
     * Get clientSecret
     *
     * @return string 
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return OauthClient
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set redirectUri
     *
     * @param string $redirectUri
     * @return OauthClient
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    
        return $this;
    }

    /**
     * Get redirectUri
     *
     * @return string 
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * Set grantTypes
     *
     * @param string $grantTypes
     * @return OauthClient
     */
    public function setGrantTypes($grantTypes)
    {
        $this->grantTypes = $grantTypes;
    
        return $this;
    }

    /**
     * Get grantTypes
     *
     * @return string 
     */
    public function getGrantTypes()
    {
        return $this->grantTypes;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return OauthClient
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
     * Set user
     *
     * @param \Etu\Core\UserBundle\Entity\User $user
     * @return OauthClient
     */
    public function setUser(\Etu\Core\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Etu\Core\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add scopes
     *
     * @param \Etu\Core\ApiBundle\Entity\OauthScope $scopes
     * @return OauthClient
     */
    public function addScope(\Etu\Core\ApiBundle\Entity\OauthScope $scopes)
    {
        $this->scopes[] = $scopes;
    
        return $this;
    }

    /**
     * Remove scopes
     *
     * @param \Etu\Core\ApiBundle\Entity\OauthScope $scopes
     */
    public function removeScope(\Etu\Core\ApiBundle\Entity\OauthScope $scopes)
    {
        $this->scopes->removeElement($scopes);
    }

    /**
     * Get scopes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getScopes()
    {
        return $this->scopes;
    }
}