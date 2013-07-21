<?php

namespace Etu\Module\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Etu\Module\ForumBundle\Entity\Permissions;

/**
 * @ORM\Table(name="etu_forum_categories")
 * @ORM\Entity
 */
class Category
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="slug", type="string", length=50)
	 */
	protected $slug;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="title", type="string", length=50)
	 */
	protected $title;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="description", type="string", length=255, nullable=true)
	 */
	protected $description;

	/**
	 * @var integer $left
	 *
	 * @ORM\Column(name="left", type="integer")
	 */
	protected $left;

	/**
	 * @var integer $depth
	 *
	 * @ORM\Column(name="depth", type="integer")
	 */
	protected $depth;

	/**
	 * @var integer $right
	 *
	 * @ORM\Column(name="right", type="integer")
	 */
	protected $right;

	/**
	 * @var integer $countThreads
	 *
	 * @ORM\Column(name="countThreads", type="integer")
	 */
	protected $countThreads;

	/**
	 * @var integer $countMessages
	 *
	 * @ORM\Column(name="countMessages", type="integer")
	 */
	protected $countMessages;

	/**
	 * @var \Etu\Module\ForumBundle\Entity\Message $lastMessage
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Module\ForumBundle\Entity\Message")
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $lastMessage;

	/**
	 * @ORM\OneToMany(targetEntity="\Etu\Module\ForumBundle\Entity\Permissions", mappedBy="category")
	 */
	protected $permissions;


	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->countMessages = 0;
		$this->countThreads = 0;
		$this->left = 0;
		$this->right = 0;
		$this->depth = 0;
		$this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $countMessages
	 * @return Category
	 */
	public function setCountMessages($countMessages)
	{
		$this->countMessages = (integer) $countMessages;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCountMessages()
	{
		return $this->countMessages;
	}

	/**
	 * @param int $countThreads
	 * @return Category
	 */
	public function setCountThreads($countThreads)
	{
		$this->countThreads = (integer) $countThreads;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCountThreads()
	{
		return $this->countThreads;
	}

	/**
	 * @param int $depth
	 * @return Category
	 */
	public function setDepth($depth)
	{
		$this->depth = (integer) $depth;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getDepth()
	{
		return $this->depth;
	}

	/**
	 * @param string $description
	 * @return Category
	 */
	public function setDescription($description)
	{
		$this->description = (string) $description;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param \Etu\Module\ForumBundle\Entity\Message $lastMessage
	 * @return Category
	 */
	public function setLastMessage(Message $lastMessage)
	{
		$this->lastMessage = $lastMessage;

		return $this;
	}

	/**
	 * @return \Etu\Module\ForumBundle\Entity\Message
	 */
	public function getlastMessage()
	{
		return $this->lastMessage;
	}

	/**
	 * @param int $left
	 * @return Category
	 */
	public function setLeft($left)
	{
		$this->left = (integer) $left;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLeft()
	{
		return $this->left;
	}

	/**
	 * @param int $right
	 * @return Category
	 */
	public function setRight($right)
	{
		$this->right = (integer) $right;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getRight()
	{
		return $this->right;
	}

	/**
	 * @param string $slug
	 * @return Category
	 */
	public function setSlug($slug)
	{
		$this->slug = (string) $slug;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSlug()
	{
		return $this->slug;
	}

	/**
	 * @param string $title
	 * @return Category
	 */
	public function setTitle($title)
	{
		$this->title = (string) $title;

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
	 * @return Etu\Module\ForumBundle\Entity\Permissions
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}
}
