<?php

namespace Etu\Core\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\User;

/**
 * @ORM\Table(name="etu_subscriptions", indexes={ @ORM\Index(name="search", columns={"user_id", "entityType", "entityId"}) })
 * @ORM\Entity
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
	 * @var \DateTime
	 *
	 * @ORM\Column(name="date", type="datetime")
	 */
	protected $date;


	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->date = new \DateTime();
	}

	/**
	 * @param \DateTime $date
	 * @return Subscription
	 */
	public function setDate(\DateTime $date)
	{
		$this->date = $date;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}

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
}
