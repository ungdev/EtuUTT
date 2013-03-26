<?php

namespace Etu\Module\BugsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\User;

/**
 * Issue
 *
 * @ORM\Table(name="etu_issues")
 * @ORM\Entity
 */
class Issue
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
	 * @var integer
	 *
	 * @ORM\Column(name="number", type="integer")
	 */
	private $number;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="title", type="string", length=100)
	 */
	private $title;

	/**
	 * @var User $user
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
	 * @ORM\JoinColumn()
	 */
	private $user;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="type", type="string", length=20)
	 */
	private $type;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="is_opened", type="boolean")
	 */
	private $isOpened;

	/**
	 * @var User $user
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
	 * @ORM\JoinColumn()
	 */
	private $assignee;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="createdAt", type="datetime")
	 */
	private $createdAt;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="updatedAt", type="datetime")
	 */
	private $updatedAt;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="closedAt", type="datetime")
	 */
	private $closedAt;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="body", type="text")
	 */
	private $body;
}
