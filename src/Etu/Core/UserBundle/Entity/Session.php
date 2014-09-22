<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="etu_users_sessions")
 * @ORM\Entity()
 */
class Session
{
    const TYPE_ORGA = 'orga';
    const TYPE_USER = 'user';

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
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $entityType;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    protected $entityId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=200)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=64)
     */
    protected $token;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $expireAt;

    /**
     * Constructor
     */
    public function __construct($type, $id)
    {
        $this->entityType = $type;
        $this->entityId = $id;
        $this->expireAt = new \DateTime('+1 year');
        $this->token = hash('sha256', time() . uniqid('', true));
    }

    public function createName()
    {
        if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $browser = @get_browser();

        $name = [];

        if ($browser) {
            $name[] = $browser->browser;
            $name[] = $browser->version;
            $name[] = $browser->platform;
        } else {
            $name[] = 'Unknown browser';
        }

        $name[] = '('. gethostbyaddr($ip) .')';
        
        $this->name = implode(' ', $name);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
        return $this;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param string $entityType
     * @return $this
     */
    public function setEntityType($entityType)
    {
        $this->entityType = $entityType;
        return $this;
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @param \DateTime $expireAt
     * @return $this
     */
    public function setExpireAt($expireAt)
    {
        $this->expireAt = $expireAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpireAt()
    {
        return $this->expireAt;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
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
