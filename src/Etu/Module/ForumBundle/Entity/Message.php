<?php

namespace Etu\Module\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Etu\Core\UserBundle\Entity\User;

/**
 * @ORM\Table(name="etu_forum_messages")
 * @ORM\Entity
 */
class Message
{
	const STATE_VISIBLE = 100;
	const STATE_HIDDEN = 200;

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
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $author;

	/**
	 * @var Category $category
	 *
	 * @ORM\ManyToOne(targetEntity="Category")
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $category;

	/**
	 * @var Thread $thread
	 *
	 * @ORM\ManyToOne(targetEntity="Thread")
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $thread;

	/**
	 * @var \DateTime
	 *
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="createdAt", type="datetime")
	 */
	protected $createdAt;

	/**
	 * @var \DateTime
	 *
	 * @Gedmo\Timestampable(on="update")
	 * @ORM\Column(name="updatedAt", type="datetime", nullable=true)
	 */
	protected $updatedAt;

	/**
	 * Current state of the comment.
	 *
	 * @var integer
	 *
	 * @ORM\Column(name="state", type="smallint")
	 */
	protected $state;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="content", type="text")
	 */
	protected $content;


	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->state = self::STATE_VISIBLE;
		$this->createdAt = new \DateTime();
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
	 * @return Message
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
	 * @param string $content
	 * @return Message
	 */
	public function setContent($content)
	{
		$this->content = (string) $content;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @param \DateTime $createdAt
	 * @return Message
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
	 * @param int $state
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	public function setState($state)
	{
		if (! in_array($state, array(self::STATE_HIDDEN, self::STATE_VISIBLE))) {
			throw new \InvalidArgumentException(
				sprintf('Invalid thread state (%s given, Message constante expected).', $state)
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
	 * @param \Etu\Module\ForumBundle\Entity\Thread $thread
	 * @return Message
	 */
	public function setThread(Thread $thread)
	{
		$this->thread = $thread;

		return $this;
	}

	/**
	 * @return \Etu\Module\ForumBundle\Entity\Thread
	 */
	public function getThread()
	{
		return $this->thread;
	}

	/**
	 * @param \DateTime $updatedAt
	 * @return Message
	 */
	public function setUpdatedAt(\DateTime $updatedAt)
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
}
