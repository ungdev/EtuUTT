<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="etu_users_badges")
 * @ORM\Entity()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class UserBadge
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
	 * @var \DateTime $deletedAt
	 *
	 * @ORM\Column(type="datetime", nullable = true)
	 */
	protected $deletedAt;

	/**
	 * @var User $user
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User", inversedBy="badges")
	 * @ORM\JoinColumn()
	 */
	protected $user;

	/**
	 * @var Badge $badge
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\Badge")
	 * @ORM\JoinColumn()
	 */
	protected $badge;


	public function __construct(Badge $badge, User $user)
	{
		$this->badge = $badge;
		$this->user = $user;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param \Etu\Core\UserBundle\Entity\Badge $badge
	 * @return $this
	 */
	public function setBadge($badge)
	{
		$this->badge = $badge;
		return $this;
	}

	/**
	 * @return \Etu\Core\UserBundle\Entity\Badge
	 */
	public function getBadge()
	{
		return $this->badge;
	}

	/**
	 * @param \DateTime $deletedAt
	 * @return $this
	 */
	public function setDeletedAt($deletedAt)
	{
		$this->deletedAt = $deletedAt;
		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getDeletedAt()
	{
		return $this->deletedAt;
	}

	/**
	 * @param \Etu\Core\UserBundle\Entity\User $user
	 * @return $this
	 */
	public function setUser($user)
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
}
