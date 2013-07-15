<?php

namespace Etu\Module\WikiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;

/**
 * @ORM\Table(name="etu_wiki_categories")
 * @ORM\Entity()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Category
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
	 * @var Category $parent
	 *
	 * @ORM\ManyToOne(targetEntity="Category")
	 * @ORM\JoinColumn()
	 */
	protected $parent;

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
	 * @var \DateTime $created
	 *
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="createdAt", type="datetime")
	 */
	protected $createdAt;

	/**
	 * @var \DateTime $deletedAt
	 *
	 * @ORM\Column(name="deletedAt", type="datetime", nullable = true)
	 */
	protected $deletedAt;

	/**
	 * Category depth
	 *
	 * @var integer
	 *
	 * @ORM\Column(name="depth", type="integer")
	 */
	protected $depth;

	public $parentId = 0;
	public $children = array();
	public $hasChildren = false;
	public $pages = array();


	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->levelToView = self::LEVEL_ASSO_MEMBER;
		$this->levelToEdit = self::LEVEL_ASSO_ADMIN;
		$this->levelToEditPermissions = self::LEVEL_ASSO_ADMIN;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $depth
	 * @return Category
	 */
	public function setDepth($depth)
	{
		$this->depth = $depth;

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
	 * @param int $levelToEdit
	 * @return Category
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
	 * @return Category
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
	 * @return Category
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
	 * @return Category
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
	 * @return Category
	 */
	public function removeParent()
	{
		$this->parent = null;

		return $this;
	}

	/**
	 * @param \Etu\Module\WikiBundle\Entity\Category $parent
	 * @return Category
	 */
	public function setParent($parent)
	{
		$this->parent = $parent;

		return $this;
	}

	/**
	 * @return \Etu\Module\WikiBundle\Entity\Category
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * @param string $title
	 * @return Category
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
	 * @return $this
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
	 * Set deletedAt
	 *
	 * @param \DateTime $deletedAt
	 * @return $this
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
