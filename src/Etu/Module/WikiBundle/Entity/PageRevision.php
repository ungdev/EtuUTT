<?php

namespace Etu\Module\WikiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Etu\Core\UserBundle\Entity\User;

/**
 * Page
 *
 * @ORM\Table(name="etu_wiki_pages_revisions")
 * @ORM\Entity()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class PageRevision
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
	 * @var integer $page
	 *
	 * @ORM\Column(name="page", type="integer", nullable=true)
	 */
	protected $page;

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
	 * @ORM\Column(name="text", type="text")
	 * @Assert\NotBlank()
	 * @Assert\Length(min = "15")
	 */
	protected $body;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="comment", type="string", length=140, nullable=true)
	 * @Assert\Length(max = "140")
	 */
	protected $comment;

	/**
	 * @var \DateTime $created
	 *
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="createdAt", type="datetime")
	 */
	protected $createdAt;

	/**
	 * @var \DateTime $updated
	 *
	 * @Gedmo\Timestampable(on="update")
	 * @ORM\Column(name="updatedAt", type="datetime")
	 */
	protected $updatedAt;

	/**
	 * @var \DateTime $deletedAt
	 *
	 * @ORM\Column(name="deletedAt", type="datetime", nullable = true)
	 */
	protected $deletedAt;

	/**
	 * Temporary variable to store page title during edition
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Temporary variable to store page category
	 *
	 * @var string
	 */
	public $category;

	/**
	 * @param string $body
	 * @return PageRevision
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
	 * @param string $comment
	 * @return PageRevision
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getComment()
	{
		return $this->comment;
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
	 * @return PageRevision
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
	 * @param int $pageId
	 * @return PageRevision
	 */
	public function setPageId($pageId)
	{
		$this->page = $pageId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getPageId()
	{
		return $this->page;
	}

    /**
     * Set page
     *
     * @param integer $page
     * @return PageRevision
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page
     *
     * @return integer
     */
    public function getPage()
    {
        return $this->page;
    }

	/**
	 * Set createdAt
	 *
	 * @param \DateTime $createdAt
	 * @return PageRevision
	 */
	public function setDate($createdAt)
	{
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * Get createdAt
	 *
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->createdAt;
	}

	/**
	 * Set createdAt
	 *
	 * @param \DateTime $createdAt
	 * @return PageRevision
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return PageRevision
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return PageRevision
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
