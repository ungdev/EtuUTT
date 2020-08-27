<?php

namespace Etu\Core\ApiBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\User;

/**
 * OauthAuthorization.
 *
 * @ORM\Table(name="oauth_authorizations")
 * @ORM\Entity
 */
class OauthAuthorization
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var OauthClient
     *
     * @ORM\ManyToOne(targetEntity="OauthClient")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $client;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var OauthScope[]
     *
     * @ORM\ManyToMany(targetEntity="OauthScope")
     * @ORM\JoinTable(name="oauth_authorizations_scopes")
     */
    private $scopes;

    /**
     * Constructor.
     *
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
     * @return OauthAuthorization
     */
    public static function createFromAuthorizationCode(OauthAuthorizationCode $code)
    {
        return new self($code->getClient(), $code->getUser(), $code->getScopes());
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
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return OauthAuthorization
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
     * Set client.
     *
     * @param OauthClient $client
     *
     * @return OauthAuthorization
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
     * @return OauthAuthorization
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
     * @return OauthAuthorization
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
     * @return \Doctrine\Common\Collections\Collection|OauthScope[]
     */
    public function getScopes()
    {
        return $this->scopes;
    }
}
