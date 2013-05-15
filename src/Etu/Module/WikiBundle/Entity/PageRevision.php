<?php

namespace Etu\Module\WikiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Etu\Core\UserBundle\Entity\User;

/**
 * Page
 *
 * @ORM\Table(name="etu_wiki_pages_revisions")
 * @ORM\Entity
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
	 * @var integer $pageId
	 *
	 * @ORM\Column(name="page_id", type="integer")
	 */
	protected $pageId;

	/**
	 * @var User $user
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
	 * @ORM\JoinColumn()
	 */
	protected $user;

	/**
	 * @var PageRevision $previous
	 *
	 * @ORM\OneToOne(targetEntity="PageRevision")
	 * @ORM\JoinColumn()
	 */
	protected $previous;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="text", type="text")
	 */
	protected $body;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="comment", type="string", length=200, nullable=true)
	 */
	protected $comment;

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
	 * @param \DateTime $date
	 * @return PageRevision
	 */
	public function setDate($date)
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
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param PageRevision $previous
	 * @return PageRevision
	 */
	public function setPrevious($previous)
	{
		$this->previous = $previous;

		return $this;
	}

	/**
	 * @return PageRevision
	 */
	public function getPrevious()
	{
		return $this->previous;
	}

	/**
	 * @param \Etu\Core\UserBundle\Entity\User $user
	 * @return Page
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
		$this->pageId = $pageId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getPageId()
	{
		return $this->pageId;
	}
}
