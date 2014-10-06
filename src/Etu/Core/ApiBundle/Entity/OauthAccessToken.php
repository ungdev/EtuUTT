<?php

namespace Etu\Core\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\User;

/**
 * @ORM\Table(name="oauth_access_tokens", indexes={ @ORM\Index(name="access_token_index", columns={ "token" }) })
 * @ORM\Entity
 */
class OauthAccessToken
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
     * @var OauthClient $client
     *
     * @ORM\ManyToOne(targetEntity="OauthClient")
     * @ORM\JoinColumn()
     */
    private $client;

    /**
     * @var User $user
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
     * @ORM\JoinColumn()
     */
    private $user;

    /**
     * @var OauthRefreshToken $refreshToken
     *
     * @ORM\ManyToOne(targetEntity="Etu\Core\ApiBundle\Entity\OauthRefreshToken")
     * @ORM\JoinColumn()
     */
    private $refreshToken;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    private $token;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $expireAt;

    /**
     * @var OauthScope[] $scopes
     *
     * @ORM\ManyToMany(targetEntity="OauthScope")
     * @ORM\JoinTable(name="oauth_access_tokens_scopes")
     */
    private $scopes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->expireAt = new \DateTime('+1 hour');
        $this->scopes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return string
     */
    public function generateToken()
    {
        return $this->token = md5(uniqid(time(), true));
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
     * Set token
     *
     * @param string $token
     * @return OauthAccessToken
     */
    public function setToken($token)
    {
        $this->token = $token;
    
        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return OauthAccessToken
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
     * Set expireAt
     *
     * @param \DateTime $expireAt
     * @return OauthAccessToken
     */
    public function setExpireAt($expireAt)
    {
        $this->expireAt = $expireAt;
    
        return $this;
    }

    /**
     * Get expireAt
     *
     * @return \DateTime 
     */
    public function getExpireAt()
    {
        return $this->expireAt;
    }

    /**
     * Set client
     *
     * @param \Etu\Core\ApiBundle\Entity\OauthClient $client
     * @return OauthAccessToken
     */
    public function setClient(\Etu\Core\ApiBundle\Entity\OauthClient $client = null)
    {
        $this->client = $client;
    
        return $this;
    }

    /**
     * Get client
     *
     * @return \Etu\Core\ApiBundle\Entity\OauthClient 
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set user
     *
     * @param \Etu\Core\UserBundle\Entity\User $user
     * @return OauthAccessToken
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
     * @return OauthRefreshToken
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param OauthRefreshToken $refreshToken
     */
    public function setRefreshToken(OauthRefreshToken $refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * Add scopes
     *
     * @param \Etu\Core\ApiBundle\Entity\OauthScope $scopes
     * @return OauthAccessToken
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
     * @param OauthScope|string $scope
     * @return bool
     */
    public function hasScope($scope)
    {
        if (is_string($scope)) {
            foreach ($this->scopes as $tokenScope) {
                if ($tokenScope->getName() == $scope) {
                    return true;
                }
            }

            return false;
        } else {
            return in_array($scope, $this->scopes->toArray());
        }
    }

    /**
     * Get scopes
     *
     * @return \Doctrine\Common\Collections\Collection|OauthScope[]
     */
    public function getScopes()
    {
        return $this->scopes;
    }
}