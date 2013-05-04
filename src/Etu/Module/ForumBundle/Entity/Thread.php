<?php

namespace Etu\Module\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\User;

/**
 * @ORM\Table(name="etu_forum_threads")
 * @ORM\Entity
 */
class Thread
{
	const STATE_OPEN = 100;
	const STATE_CLOSED = 200;
	const STATE_HIDDEN = 300;

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var User $user
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
	 * @ORM\JoinColumn()
	 */
	protected $author;

	/**
	 * @var Category $category
	 *
	 * @ORM\ManyToOne(targetEntity="Category")
	 * @ORM\JoinColumn()
	 */
	protected $category;

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
	 * @var \DateTime $createdAt
	 *
	 * @ORM\Column(name="createdAt", type="datetime")
	 */
	protected $createdAt;

	/**
	 * Current state of the comment.
	 *
	 * @var integer
	 *
	 * @ORM\Column(name="state", type="smallint")
	 */
	protected $state;

	/**
	 * @var integer $countMessages
	 *
	 * @ORM\Column(name="countMessages", type="integer")
	 */
	protected $countMessages;

	/**
	 * @var Message $lastMesssage
	 *
	 * @ORM\Column(name="lastMesssage", type="object", nullable=true)
	 */
	protected $lastMesssage;


	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->state = self::STATE_OPEN;
		$this->createdAt = new \DateTime();
		$this->countMessages = 0;
	}

	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param User $author
	 * @return $this
	 */
	public function setAuthor(User $author)
	{
		$this->author = $author;

		return $this;
	}

	/**
	 * @return User
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * @param \Etu\Module\ForumBundle\Entity\Category $category
	 * @return Thread
	 */
	public function setCategory(Category $category)
	{
		$this->category = $category;

		return $this;
	}

	/**
	 * @return \Etu\Module\ForumBundle\Entity\Category
	 */
	public function getCategory()
	{
		return $this->category;
	}

	/**
	 * @param int $countMessages
	 * @return Thread
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
	 * @param \DateTime $createdAt
	 * @return Thread
	 */
	public function setCreatedAt(\DateTime $createdAt)
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
	 * @param \Etu\Module\ForumBundle\Entity\Message $lastMesssage
	 * @return Thread
	 */
	public function setLastMesssage(Message $lastMesssage)
	{
		$this->lastMesssage = $lastMesssage;

		return $this;
	}

	/**
	 * @return \Etu\Module\ForumBundle\Entity\Message
	 */
	public function getLastMesssage()
	{
		return $this->lastMesssage;
	}

	/**
	 * @param string $slug
	 * @return Thread
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
	 * @param int $state
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	public function setState($state)
	{
		if (! in_array($state, array(self::STATE_HIDDEN, self::STATE_CLOSED, self::STATE_OPEN))) {
			throw new \InvalidArgumentException(
				sprintf('Invalid thread state (%s given, Thread constante expected).', $state)
			);
		}

		$this->state = $state;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @param string $title
	 * @return Thread
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
}