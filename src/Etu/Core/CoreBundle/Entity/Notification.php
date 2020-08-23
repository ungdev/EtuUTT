<?php

namespace Etu\Core\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="etu_notifications")
 * @ORM\Entity()
 */
class Notification
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $authorId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    protected $entityType;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $entityId;

    /**
     * Template helper: class loaded to display the notification.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $helper;

    /**
     * List of entities in the notification (given to the helper).
     *
     * @var array
     *
     * @ORM\Column(type="array")
     */
    protected $entities;

    /**
     * Source module.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $module;

    /**
     * Is a super-notification ?
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $isSuper;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $expiration;

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
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $deletedAt;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->expiration = new \DateTime();
        $this->isSuper = false;
        $this->authorId = 0;
    }

    /**
     * @param int $authorId
     *
     * @return Notification
     */
    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;

        return $this;
    }

    /**
     * @return int
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }

    /**
     * @return Notification
     */
    public function setEntities(array $entities)
    {
        $this->entities = $entities;

        return $this;
    }

    /**
     * @param object $entity
     *
     * @return Notification
     */
    public function addEntity($entity)
    {
        $this->entities[] = $entity;

        return $this;
    }

    /**
     * @param object $entity
     *
     * @return Notification
     */
    public function removeEntity($entity)
    {
        if ($key = array_search($entity, $this->entities)) {
            unset($this->entities[$key]);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function countEntities()
    {
        return count($this->entities);
    }

    /**
     * @return object
     */
    public function getFirstEntity()
    {
        return $this->getEntity(1);
    }

    /**
     * @param int $number
     *
     * @return object
     */
    public function getEntity($number)
    {
        return (isset($this->entities[$number - 1])) ? $this->entities[$number - 1] : false;
    }

    /**
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @param int $entityId
     *
     * @return Notification
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
     *
     * @return Notification
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
     * @param \DateTime $expiration
     *
     * @return Notification
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @param string $helper
     *
     * @return Notification
     */
    public function setHelper($helper)
    {
        $this->helper = $helper;

        return $this;
    }

    /**
     * @return string
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param bool $isSuper
     *
     * @return Notification
     */
    public function setIsSuper($isSuper)
    {
        $this->isSuper = $isSuper;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsSuper()
    {
        return $this->isSuper;
    }

    /**
     * @param string $module
     *
     * @return Notification
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return Notification
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
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->createdAt;
    }

    /**
     * @return bool
     */
    public function isNew(\DateTime $lastVisitHome)
    {
        return $lastVisitHome < $this->createdAt;
    }

    /**
     * Set deletedAt.
     *
     * @param \DateTime $deletedAt
     *
     * @return Notification
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
}
