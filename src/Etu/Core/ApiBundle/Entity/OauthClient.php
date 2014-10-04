<?php

namespace Etu\Core\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * OauthClients
 *
 * @ORM\Table(name="oauth_clients")
 * @ORM\Entity
 */
class OauthClient
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="client_id", type="string", length=80)
     */
    private $clientId;

    /**
     * @var string
     *
     * @ORM\Column(name="client_secret", type="string", length=80, nullable=false)
     */
    private $clientSecret;

    /**
     * @var string
     *
     * @ORM\Column(name="redirect_uri", type="string", length=2000, nullable=false)
     */
    private $redirectUri;

    /**
     * @var string
     *
     * @ORM\Column(name="grant_types", type="string", length=80, nullable=true)
     */
    private $grantTypes;

    /**
     * @var string
     *
     * @ORM\Column(name="scope", type="string", length=100, nullable=true)
     */
    private $scope;

    /**
     * @var string
     *
     * @ORM\Column(name="user_id", type="string", length=80, nullable=true)
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=80)
     */
    private $name;

    /**
     * @var UploadedFile
     *
     * @Assert\Image(maxSize = "2M", minWidth = 150, minHeight = 150)
     */
    public $file;

    public $scopesList = [];


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

    public function generateClientId()
    {
        return $this->clientId = mt_rand(100000000, 2100000000) * 25;
    }

    public function generateClientSecret()
    {
        return $this->clientSecret = md5(uniqid(time(), true));
    }

    /**
     * @return string
     */
    public function injectScopesList()
    {
        $this->scopesList[] = 'public';
        return $this->scope = implode(' ', array_unique($this->scopesList));
    }

    /**
     * @return string
     */
    public function deductScopesList()
    {
        return $this->scopesList = explode(' ', $this->scope);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->clientId . '.png';
    }

    /**
     * @param string $clientId
     * @return $this
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
     * Set scope
     *
     * @param string $scope
     * @return OauthClient
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Get scopes
     *
     * @return array
     */
    public function getScopeList()
    {
        return explode(' ', $this->scope);
    }

    /**
     * Set userId
     *
     * @param string $userId
     * @return OauthClient
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $name
     * @return $this
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
}