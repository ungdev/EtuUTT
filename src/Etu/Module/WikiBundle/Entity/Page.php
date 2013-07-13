<?php

namespace Etu\Module\WikiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;

/**
 * Page
 *
 * @ORM\Table(name="etu_wiki_pages")
 * @ORM\Entity()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Page
{
	const LEVEL_CONNECTED = 0;
	const LEVEL_ASSO_MEMBER = 10;
	const LEVEL_ASSO_ADMIN = 15;
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
	 * @var Category $category
	 *
	 * @ORM\ManyToOne(targetEntity="Category")
	 * @ORM\JoinColumn()
	 */
	protected $category;

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
	 * @var string
	 *
	 * @ORM\Column(name="title", type="string", length=50)
	 * @Assert\NotBlank()
	 * @Assert\Length(min = "10", max = "50")
	 */
	protected $title;

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
	 * Is home of the organization ?
	 *
	 * @var boolean
	 *
	 * @ORM\Column(name="isHome", type="boolean")
	 */
	protected $isHome;

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

	public $body;
	public $comment;
	public $parent;


	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->isHome = false;
		$this->isDeleted = false;
		$this->levelToView = self::LEVEL_ASSO_MEMBER;
		$this->levelToEdit = self::LEVEL_ASSO_ADMIN;
		$this->levelToEditPermissions = self::LEVEL_ASSO_ADMIN;
		$this->createdAt = new \DateTime();
	}

	/**
	 * @return PageRevision
	 */
	public function createRevision()
	{
		$revision = new PageRevision();
		$revision->setPageId($this->getId());

		return $revision;
	}

	/**
	 * @return $this
	 */
	public function putToRoot()
	{
		$this->category = null;

		return $this;
	}

	/**
	 * @param \Etu\Module\WikiBundle\Entity\Category $category
	 * @return Page
	 */
	public function setCategory($category)
	{
		$this->category = $category;

		return $this;
	}

	/**
	 * @return \Etu\Module\WikiBundle\Entity\Category
	 */
	public function getCategory()
	{
		return $this->category;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $isHome
	 * @return Page
	 */
	public function setIsHome($isHome)
	{
		$this->isHome = $isHome;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getIsHome()
	{
		return $this->isHome;
	}

	/**
	 * @param int $levelToEdit
	 * @return Page
	 */
	public function setLevelToEdit($levelToEdit)
	{
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
	public function setOrga($orga)
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
	 * @param \Etu\Module\WikiBundle\Entity\PageRevision $revision
	 * @return Page
	 */
	public function setRevision($revision)
	{
		$this->revision = $revision;

		return $this;
	}

	/**
	 * @return \Etu\Module\WikiBundle\Entity\PageRevision
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Page
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
     * @return Page
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
     * @return Page
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
