<?php

namespace Etu\Module\WikiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;

/**
 * Page
 *
 * @ORM\Table(name="etu_wiki_pages")
 * @ORM\Entity
 */
class Page
{
	const LEVEL_CONNECTED = 0;
	const LEVEL_ASSO = 10;
	const LEVEL_ADMIN = 20;
	const LEVEL_UNREACHABLE = 30;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var PageRevision $revision
	 *
	 * @ORM\OneToOne(targetEntity="PageRevision")
	 * @ORM\JoinColumn()
	 */
	protected $revision;

	/**
	 * @var Organization $orga
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\Organization")
	 * @ORM\JoinColumn()
	 */
	protected $orga;

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
	 * @ORM\Column(name="title", type="string", length=100)
	 */
	protected $title;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="date", type="datetime")
	 */
	protected $date;

	/**
	 * @var integer $left
	 *
	 * @ORM\Column(name="left", type="integer")
	 */
	protected $left;

	/**
	 * @var integer $right
	 *
	 * @ORM\Column(name="right", type="integer")
	 */
	protected $right;

	/**
	 * Required level to view this page
	 *
	 * @var integer
	 *
	 * @ORM\Column(name="levelToView", type="integer")
	 */
	protected $levelToView;

	/**
	 * Required level to edit this page
	 *
	 * @var integer
	 *
	 * @ORM\Column(name="levelToEdit", type="integer")
	 */
	protected $levelToEdit;

	/**
	 * Required level to edit permissions of this page
	 *
	 * @var integer
	 *
	 * @ORM\Column(name="levelToEditPermissions", type="integer")
	 */
	protected $levelToEditPermissions;

	/**
	 * Required level to create children for this page
	 *
	 * @var integer
	 *
	 * @ORM\Column(name="levelToCreate", type="integer")
	 */
	protected $levelToCreate;

	/**
	 * Required level to delete this page
	 *
	 * @var integer
	 *
	 * @ORM\Column(name="levelToDelete", type="integer")
	 */
	protected $levelToDelete;

	/**
	 * Is home of the organization ?
	 *
	 * @var integer
	 *
	 * @ORM\Column(name="home", type="integer")
	 */
	protected $isHome;


	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->date = new \DateTime();
		$this->levelToCreate = self::LEVEL_ASSO;
		$this->levelToDelete = self::LEVEL_ASSO;
		$this->levelToEdit = self::LEVEL_ASSO;
		$this->levelToEditPermissions = self::LEVEL_ASSO;
		$this->levelToView = self::LEVEL_CONNECTED;
		$this->isHome = false;
	}

	/**
	 * @return PageRevision
	 */
	public function createRevision()
	{
		$revision = new PageRevision();
		$revision->setPageId($this->getId());
		$revision->setPrevious($this->getRevision());

		return $revision;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param \DateTime $date
	 * @return Page
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
	 * @param int $left
	 * @return Page
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
	 * @return Page
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
	 * @param int $levelToCreate
	 * @return Page
	 */
	public function setLevelToCreate($levelToCreate)
	{
		if (! in_array($levelToCreate, array(
			self::LEVEL_ADMIN, self::LEVEL_ASSO, self::LEVEL_CONNECTED
		))) {
			$levelToCreate = self::LEVEL_ASSO;
		}

		$this->levelToCreate = $levelToCreate;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLevelToCreate()
	{
		return $this->levelToCreate;
	}

	/**
	 * @param int $levelToDelete
	 * @return Page
	 */
	public function setLevelToDelete($levelToDelete)
	{
		if (! in_array($levelToDelete, array(
			self::LEVEL_ADMIN, self::LEVEL_ASSO, self::LEVEL_CONNECTED
		))) {
			$levelToDelete = self::LEVEL_ASSO;
		}

		$this->levelToDelete = $levelToDelete;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLevelToDelete()
	{
		return $this->levelToDelete;
	}

	/**
	 * @param int $levelToEdit
	 * @return Page
	 */
	public function setLevelToEdit($levelToEdit)
	{
		if (! in_array($levelToEdit, array(
			self::LEVEL_ADMIN, self::LEVEL_ASSO, self::LEVEL_CONNECTED
		))) {
			$levelToEdit = self::LEVEL_ASSO;
		}

		$this->levelToEdit = $levelToEdit;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLevelToEdit()
	{
		return $this->levelToEdit;
	}

	/**
	 * @param int $levelToEditPermissions
	 * @return Page
	 */
	public function setLevelToEditPermissions($levelToEditPermissions)
	{
		if (! in_array($levelToEditPermissions, array(
			self::LEVEL_ADMIN, self::LEVEL_ASSO, self::LEVEL_CONNECTED
		))) {
			$levelToEditPermissions = self::LEVEL_CONNECTED;
		}

		$this->levelToEditPermissions = $levelToEditPermissions;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLevelToEditPermissions()
	{
		return $this->levelToEditPermissions;
	}

	/**
	 * @param int $levelToView
	 * @return Page
	 */
	public function setLevelToView($levelToView)
	{
		if (! in_array($levelToView, array(
			self::LEVEL_ADMIN, self::LEVEL_ASSO, self::LEVEL_CONNECTED
		))) {
			$levelToView = self::LEVEL_CONNECTED;
		}

		$this->levelToView = $levelToView;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLevelToView()
	{
		return $this->levelToView;
	}

	/**
	 * @param \Etu\Core\UserBundle\Entity\Organization $orga
	 * @return Page
	 */
	public function setOrga(Organization $orga)
	{
		$this->orga = $orga;

		return $this;
	}

	/**
	 * @return \Etu\Core\UserBundle\Entity\Organization
	 */
	public function getOrga()
	{
		return $this->orga;
	}

	/**
	 * @param PageRevision $revision
	 * @return Page
	 */
	public function setRevision(PageRevision $revision)
	{
		$this->revision = $revision;

		return $this;
	}

	/**
	 * @return PageRevision
	 */
	public function getRevision()
	{
		return $this->revision;
	}

	/**
	 * @param string $title
	 * @return Page
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
	 * @param int $isHome
	 * @return Page
	 */
	public function setIsHome($isHome)
	{
		$this->isHome = (boolean) $isHome;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getIsHome()
	{
		return $this->isHome;
	}
}
