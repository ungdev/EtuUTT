<?php

namespace Etu\Module\BugsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\User;

/**
 * Page
 *
 * @ORM\Table(name="etu_wiki_pages")
 * @ORM\Entity
 */
class Page
{
	/**
	 * Issues criticalities
	 */
	const CRITICALITY_TYPO = 'typo';
	const CRITICALITY_VISUAL = 'visual';
	const CRITICALITY_MINOR = 'minor';
	const CRITICALITY_MAJOR = 'major';
	const CRITICALITY_CRITICAL = 'critical';
	const CRITICALITY_SECURITY = 'security';

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="title", type="string", length=100)
	 */
	protected $title;

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
	 * @ORM\Column(name="criticality", type="string", length=20)
	 */
	protected $criticality;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="is_opened", type="boolean")
	 */
	protected $isOpened;

	/**
	 * @var User $user
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
	 * @ORM\JoinColumn()
	 */
	protected $assignee;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="createdAt", type="datetime")
	 */
	protected $createdAt;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="updatedAt", type="datetime", nullable=true)
	 */
	protected $updatedAt;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="closedAt", type="datetime", nullable=true)
	 */
	protected $closedAt;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="body", type="text", nullable=true)
	 */
	protected $body;
}
