<?php

namespace Etu\Module\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="etu_forum_threads")
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Thread
{
    public const STATE_OPEN = 100;
    public const STATE_CLOSED = 200;
    public const STATE_HIDDEN = 300;

    public const WEIGHT_BASIC = 100;
    public const WEIGHT_STICKY = 200;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    protected $deletedAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $author;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(onDelete="SET NULL")
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
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="createdAt", type="datetime")
     */
    protected $createdAt;

    /**
     * Current state of the comment.
     *
     * @var int
     *
     * @ORM\Column(name="state", type="smallint")
     */
    protected $state;

    /**
     * @var int
     *
     * @ORM\Column(name="weight", type="smallint")
     */
    protected $weight;

    /**
     * @var int
     *
     * @ORM\Column(name="countMessages", type="integer")
     */
    protected $countMessages;

    /**
     * @var \Etu\Module\ForumBundle\Entity\Message
     *
     * @ORM\OneToOne(targetEntity="\Etu\Module\ForumBundle\Entity\Message", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $lastMessage;

    /**
     * @ORM\OneToMany(targetEntity="\Etu\Module\ForumBundle\Entity\View", mappedBy="thread")
     */
    protected $viewed;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->state = self::STATE_OPEN;
        $this->weight = self::WEIGHT_BASIC;
        $this->createdAt = new \DateTime();
        $this->countMessages = 0;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime $createdAt
     * @param mixed     $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @param User $author
     *
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
     *
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
     *
     * @return Thread
     */
    public function setCountMessages($countMessages)
    {
        $this->countMessages = (int) $countMessages;

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
     *
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
     *
     * @return Thread
     */
    public function setLastMessage(Message $lastMessage)
    {
        $this->lastMessage = $lastMessage;

        return $this;
    }

    /**
     * @return \Etu\Module\ForumBundle\Entity\Message
     */
    public function getLastMessage()
    {
        return $this->lastMessage;
    }

    /**
     * @param string $slug
     *
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
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setState($state)
    {
        if (!in_array($state, [self::STATE_HIDDEN, self::STATE_CLOSED, self::STATE_OPEN])) {
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
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setWeight($weight)
    {
        if ($weight == null) {
            $weight = self::WEIGHT_BASIC;
        }
        if (!in_array($weight, [self::WEIGHT_BASIC, self::WEIGHT_STICKY])) {
            throw new \InvalidArgumentException(
                sprintf('Invalid thread weight (%s given, Thread constante expected).', $weight)
            );
        }

        $this->weight = $weight;

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
     *
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

    /**
     * @return View
     */
    public function getViewed()
    {
        return $this->viewed;
    }
}
