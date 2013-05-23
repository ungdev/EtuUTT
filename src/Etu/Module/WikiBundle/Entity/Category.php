<?php

namespace Etu\Module\WikiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;

/**
 * Category
 *
 * @ORM\Table(name="etu_wiki_categories")
 * @ORM\Entity
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
	 * @ORM\OneToOne(targetEntity="Category")
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
	 * @ORM\Column(name="title", type="string", length=100)
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
}
