<?php

namespace Etu\Module\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="etu_forum_views")
 * @ORM\Entity
 */
class View
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User $user
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $user;

    /**
     * @var Thread $thread
     *
     * @ORM\ManyToOne(targetEntity="thread")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $thread;


    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $user
     * @return View
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int $thread
     * @return View
     */
    public function setThread($thread)
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * @return Thread
     */
    public function getThread()
    {
        return $this->thread;
    }
}
