<?php

namespace Etu\Module\BugsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\User;

/**
 * Comment
 *
 * @ORM\Table(name="etu_issues_comments")
 * @ORM\Entity
 */
class Comment
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
	 * @var Issue $user
	 *
	 * @ORM\ManyToOne(targetEntity="Issue")
	 * @ORM\JoinColumn()
	 */
	protected $issue;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     */
    protected $body;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    protected $createdAt;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="updatedAt", type="datetime")
	 */
	protected $updatedAt;

	/**
	 * Is this comment and update of the issue or a real user comment?
	 *
	 * @var boolean
	 *
	 * @ORM\Column(name="isStateUpdate", type="boolean")
	 */
	protected $isStateUpdate = false;


	public function __construct()
	{
		$this->createdAt = new \DateTime();
		$this->updatedAt = new \DateTime();
	}

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param \stdClass $user
     * @return Comment
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \stdClass
     */
    public function getUser()
    {
        return $this->user;
    }

	/**
	 * @return \Etu\Module\BugsBundle\Entity\Issue
	 */
	public function getIssue()
	{
		return $this->issue;
	}

	/**
	 * @param \Etu\Module\BugsBundle\Entity\Issue $issue
	 * @return Comment
	 */
	public function setIssue($issue)
	{
		$this->issue = $issue;

		return $this;
	}

    /**
     * Set body
     *
     * @param string $body
     * @return Comment
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Comment
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
     * @return Comment
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
	 * @param boolean $isStateUpdate
	 * @return Comment
	 */
	public function setIsStateUpdate($isStateUpdate)
	{
		$this->isStateUpdate = $isStateUpdate;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getIsStateUpdate()
	{
		return $this->isStateUpdate;
	}
}
