<?php

namespace Etu\Module\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="etu_forum_categoriesViews")
 * @ORM\Entity
 */
class CategoryView
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
     * @var Category $category
     *
     * @ORM\ManyToOne(targetEntity="category", inversedBy="categoryViewed")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $category;


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
     * @param int $category
     * @return CategoryView
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }
}
