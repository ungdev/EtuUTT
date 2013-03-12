<?php

namespace Etu\Module\WikiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Page
 *
 * @ORM\Table(name="wiki_pages")
 * @ORM\Entity
 */
class Page
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

	/**
	 * @var Category $category
	 *
	 * @ORM\ManyToOne(targetEntity="Category")
	 * @ORM\JoinColumn()
	 */
    private $category;

	/**
	 * @var Revision $revision
	 *
	 * @ORM\OneToOne(targetEntity="Revision")
	 * @ORM\JoinColumn()
	 */
	private $revision;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="slug", type="string", length=255)
	 */
	private $slug;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="slug", type="string", length=255, nullable=true)
	 */
	private $redirection;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="views", type="integer")
	 */
	private $views;
}
