<?php

namespace Etu\Module\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;

/**
 * @ORM\Table(name="etu_forum_permissions")
 * @ORM\Entity
 */
class Permissions
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="permissions")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $category;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\Organization")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $user;

    /**
     * @var int
     *
     * @ORM\Column(name="read", type="integer")
     */
    protected $read;

    /**
     * @var int
     *
     * @ORM\Column(name="post", type="integer")
     */
    protected $post;

    /**
     * @var int
     *
     * @ORM\Column(name="answer", type="integer")
     */
    protected $answer;

    /**
     * @var int
     *
     * @ORM\Column(name="edit", type="integer")
     */
    protected $edit;

    /**
     * @var int
     *
     * @ORM\Column(name="sticky", type="integer")
     */
    protected $sticky;

    /**
     * @var int
     *
     * @ORM\Column(name="lock", type="integer")
     */
    protected $lock;

    /**
     * @var int
     *
     * @ORM\Column(name="move", type="integer")
     */
    protected $move;

    /**
     * @var int
     *
     * @ORM\Column(name="delete", type="integer")
     */
    protected $delete;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer")
     */
    protected $type;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->read = 0;
        $this->post = 0;
        $this->answer = 0;
        $this->edit = 0;
        $this->sticky = 0;
        $this->lock = 0;
        $this->move = 0;
        $this->type = 1;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * @return int
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @return int
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @return int
     */
    public function getEdit()
    {
        return $this->edit;
    }

    /**
     * @return int
     */
    public function getSticky()
    {
        return $this->sticky;
    }

    /**
     * @return int
     */
    public function getLock()
    {
        return $this->lock;
    }

    /**
     * @return int
     */
    public function getMove()
    {
        return $this->move;
    }

    /**
     * @return int
     */
    public function getDelete()
    {
        return $this->delete;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return \Etu\Core\UserBundle\Entity\Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @return \Etu\Core\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
