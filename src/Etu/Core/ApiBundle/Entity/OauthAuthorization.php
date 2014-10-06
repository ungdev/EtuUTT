<?php

namespace Etu\Core\ApiBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\User;

/**
 * OauthAuthorization
 *
 * @ORM\Table(name="oauth_authorizations")
 * @ORM\Entity
 */
class OauthAuthorization
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
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var OauthScope[] $scopes
     *
     * @ORM\ManyToMany(targetEntity="OauthScope")
     * @ORM\JoinTable(name="oauth_authorizations_scopes")
     */
    private $scopes;

    /**
     * Constructor
     *
     * @param OauthClient $client
     * @param User $user
     * @param array|Collection $scopes
     */
    public function __construct(OauthClient $client, User $user, $scopes)
    {
        $this->client = $client;
        $this->user = $user;
        $this->createdAt = new \DateTime();
        $this->scopes = $scopes;
    }

    /**
     * @param OauthAuthorizationCode $code
     * @return OauthAuthorization
     */
    public static function createFromAuthorizationCode(OauthAuthorizationCode $code)
    {
        return new self($code->getClient(), $code->getUser(), $code->getScopes());
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return OauthAuthorization
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
     * Set client
     *
     * @param \Etu\Core\ApiBundle\Entity\OauthClient $client
     * @return OauthAuthorization
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
     * @return OauthAuthorization
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
     * @return OauthAuthorization
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
     * @return \Doctrine\Common\Collections\Collection|OauthScope[]
     */
    public function getScopes()
    {
        return $this->scopes;
    }
}