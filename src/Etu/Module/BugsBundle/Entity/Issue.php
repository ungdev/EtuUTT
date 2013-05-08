<?php

namespace Etu\Module\BugsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\User;

/**
 * Issue
 *
 * @ORM\Table(name="etu_issues")
 * @ORM\Entity
 */
class Issue
{
	/**
	 * Issues criticalities
	 */
	const CRITICALITY_TYPO = 'typo';
	const CRITICALITY_VISUAL = 'visual';
	const CRITICALITY_MINOR = 'minor';
	const CRITICALITY_MAJOR = 'major';
	const CRITICALITY_CRITICAL = 'critical';
	const CRITICALITY_SECURITY = 'security';

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="title", type="string", length=100)
	 */
	protected $title;

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
	 * @ORM\Column(name="criticality", type="string", length=20)
	 */
	protected $criticality;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="is_opened", type="boolean")
	 */
	protected $isOpened;

	/**
	 * @var User $user
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
	 * @ORM\JoinColumn()
	 */
	protected $assignee;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="createdAt", type="datetime")
	 */
	protected $createdAt;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="updatedAt", type="datetime", nullable=true)
	 */
	protected $updatedAt;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="closedAt", type="datetime", nullable=true)
	 */
	protected $closedAt;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="body", type="text", nullable=true)
	 */
	protected $body;


	public function __construct()
	{
		$this->createdAt = new \DateTime();
		$this->isOpened = true;
	}

	/**
	 * @param \Etu\Core\UserBundle\Entity\User $assignee
	 * @return Issue
	 */
	public function setAssignee($assignee)
	{
		$this->assignee = $assignee;

		return $this;
	}

	/**
	 * Close the bug
	 */
	public function close()
	{
		$this->setOpen(false);
		$this->setUpdatedAt(new \DateTime());
		$this->setClosedAt(new \DateTime());
	}

	/**
	 * Open the bug
	 */
	public function open()
	{
		$this->setOpen(true);
		$this->setUpdatedAt(new \DateTime());
	}

	/**
	 * @return \Etu\Core\UserBundle\Entity\User
	 */
	public function getAssignee()
	{
		return $this->assignee;
	}

	/**
	 * @param string $body
	 * @return Issue
	 */
	public function setBody($body)
	{
		$this->body = $body;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * @param \DateTime $closedAt
	 * @return Issue
	 */
	public function setClosedAt($closedAt)
	{
		$this->closedAt = $closedAt;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getClosedAt()
	{
		return $this->closedAt;
	}

	/**
	 * @param \DateTime $createdAt
	 * @return Issue
	 */
	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param boolean $isOpened
	 * @return Issue
	 */
	public function setIsOpened($isOpened)
	{
		$this->isOpened = $isOpened;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getIsOpened()
	{
		return $this->isOpened;
	}

	/**
	 * @param $isOpen
	 * @return $this
	 */
	public function setOpen($isOpen)
	{
		$this->isOpened = $isOpen;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isOpen()
	{
		return $this->isOpened;
	}

	/**
	 * @param string $title
	 * @return Issue
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $type
	 * @return Issue
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param \DateTime $updatedAt
	 * @return Issue
	 */
	public function setUpdatedAt($updatedAt)
	{
		$this->updatedAt = $updatedAt;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}

	/**
	 * @param \Etu\Core\UserBundle\Entity\User $user
	 * @return Issue
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

	/**
	 * @param string $criticality
	 * @return Issue
	 */
	public function setCriticality($criticality)
	{
		if (in_array($criticality, array(
			self::CRITICALITY_CRITICAL,
			self::CRITICALITY_MAJOR,
			self::CRITICALITY_MINOR,
			self::CRITICALITY_SECURITY,
			self::CRITICALITY_VISUAL,
			self::CRITICALITY_TYPO,
		))) {
				$this->criticality = $criticality;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCriticality()
	{
		return $this->criticality;
	}
}
