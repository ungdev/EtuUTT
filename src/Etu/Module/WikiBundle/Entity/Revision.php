<?php

namespace Etu\Module\WikiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Revision
 *
 * @ORM\Table(name="wiki_revisions")
 * @ORM\Entity
 */
class Revision
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
	 * @var Page $page
	 *
	 * @ORM\ManyToOne(targetEntity="Page")
	 * @ORM\JoinColumn()
	 */
    private $page;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=255)
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=255)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=15)
     */
    private $hash;
}
