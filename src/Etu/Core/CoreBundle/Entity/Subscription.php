<?php

namespace Etu\Core\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Etu\Core\UserBundle\Entity\User;

/**
 * @ORM\Table(name="etu_subscriptions")
 * @ORM\Entity()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Subscription
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
	 * @var User $user
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
	 * @ORM\JoinColumn()
	 */
	protected $user;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="entityType", type="string", length=50)
	 */
	protected $entityType;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="entityId", type="integer")
	 */
	protected $entityId;

	/**
	 * @var \DateTime $created
	 *
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="createdAt", type="datetime")
	 */
	protected $createdAt;

	/**
	 * @var \DateTime $deletedAt
	 *
	 * @ORM\Column(name="deletedAt", type="datetime", nullable = true)
	 */
	protected $deletedAt;

	/**
	 * @param int $entityId
	 * @return Subscription
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
	 * @return Subscription
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
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param \Etu\Core\UserBundle\Entity\User $user
	 * @return Subscription
	 */
	public function setUser(User $user)
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * @return \Etu\Core\UserBundle\Entity\User
	 */
	public function getUser()
	{
		return $this->user;
	}

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Subscription
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
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return Subscription
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }
}
