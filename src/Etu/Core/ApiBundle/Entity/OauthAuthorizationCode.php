<?php

namespace Etu\Core\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\User;

/**
 * OauthAuthorizationCodes.
 *
 * @ORM\Table(name="oauth_authorization_codes", indexes={ @ORM\Index(name="authorization_code_index", columns={ "code" }) })
 * @ORM\Entity
 */
class OauthAuthorizationCode
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
     * @var OauthClient
     *
     * @ORM\ManyToOne(targetEntity="OauthClient")
     * @ORM\JoinColumn()
     */
    private $client;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
     * @ORM\JoinColumn()
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    private $code;

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
     * @var OauthScope[]
     *
     * @ORM\ManyToMany(targetEntity="OauthScope")
     * @ORM\JoinTable(name="oauth_authorization_codes_scopes")
     */
    private $scopes;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->expireAt = new \DateTime('+30 seconds');
        $this->scopes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return string
     */
    public function generateCode()
    {
        return $this->code = md5(uniqid(time(), true));
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
     * Set code.
     *
     * @param string $code
     *
     * @return OauthAuthorizationCode
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return OauthAuthorizationCode
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
     * Set expireAt.
     *
     * @param \DateTime $expireAt
     *
     * @return OauthAuthorizationCode
     */
    public function setExpireAt($expireAt)
    {
        $this->expireAt = $expireAt;

        return $this;
    }

    /**
     * Get expireAt.
     *
     * @return \DateTime
     */
    public function getExpireAt()
    {
        return $this->expireAt;
    }

    /**
     * Set client.
     *
     * @param OauthClient $client
     *
     * @return OauthAuthorizationCode
     */
    public function setClient(OauthClient $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return OauthClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return OauthAuthorizationCode
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
     * @param OauthScope $scopes
     *
     * @return OauthAuthorizationCode
     */
    public function addScope(OauthScope $scopes)
    {
        $this->scopes[] = $scopes;

        return $this;
    }

    /**
     * Remove scopes.
     *
     * @param OauthScope $scopes
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
