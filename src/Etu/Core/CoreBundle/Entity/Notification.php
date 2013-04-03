<?php

namespace Etu\Core\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\User;

/**
 * @ORM\Table(name="etu_notifications", indexes={ @ORM\Index(name="search", columns={ "user_id" }) })
 * @ORM\Entity
 */
class Notification
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
	 * Template helper: class loaded to display the notification
	 *
	 * @var string
	 *
	 * @ORM\Column(name="helper", type="string", length=100)
	 */
	protected $helper;

	/**
	 * List of entities in the notification (given to the
	 *
	 * @var array
	 *
	 * @ORM\Column(name="entities", type="array")
	 */
	protected $entities;

	/**
	 * Source module
	 *
	 * @var string
	 *
	 * @ORM\Column(name="module", type="string", length=100)
	 */
	protected $module;

	/**
	 * Create or last update date
	 *
	 * @var \DateTime
	 *
	 * @ORM\Column(name="date", type="datetime")
	 */
	protected $date;

	/**
	 * Is a super-notification ?
	 *
	 * @var boolean
	 *
	 * @ORM\Column(name="isSuper", type="boolean")
	 */
	protected $isSuper;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="expiration", type="datetime")
	 */
	protected $expiration;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="isNew", type="boolean")
	 */
	protected $isNew;


	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->date = new \DateTime();
		$this->expiration = new \DateTime();
		$this->isSuper = false;
		$this->isNew = true;
	}

	/**
	 * @param \DateTime $date
	 * @return Notification
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
	 * @param array $entities
	 * @return Notification
	 */
	public function setEntities(array $entities)
	{
		$this->entities = $entities;

		return $this;
	}

	/**
	 * @param object $entity
	 * @return Notification
	 */
	public function addEntity($entity)
	{
		$this->entities[] = $entity;

		return $this;
	}

	/**
	 * @param object $entity
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
	 * @return array
	 */
	public function getEntities()
	{
		return $this->entities;
	}

	/**
	 * @param \DateTime $expiration
	 * @return Notification
	 */
	public function setExpiration(\DateTime $expiration)
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
	 * @param boolean $isNew
	 * @return Notification
	 */
	public function setIsNew($isNew)
	{
		$this->isNew = $isNew;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getIsNew()
	{
		return $this->isNew;
	}

	/**
	 * @param boolean $isSuper
	 * @return Notification
	 */
	public function setIsSuper($isSuper)
	{
		$this->isSuper = $isSuper;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getIsSuper()
	{
		return $this->isSuper;
	}

	/**
	 * @param string $module
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
	 * @param \Etu\Core\UserBundle\Entity\User $user
	 * @return Notification
	 */
	public function setUser($user)
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
