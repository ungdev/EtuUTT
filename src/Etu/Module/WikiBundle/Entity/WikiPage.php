<?php

namespace Etu\Module\WikiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="etu_wiki_pages")
 * @ORM\MappedSuperclass
 */
class WikiPage
{

    const RIGHT_ADMIN = 0;
    const RIGHT_ORGA_ADMIN = 100;
    const RIGHT_ORGA_MEMBER = 200;
    const RIGHT_STUDENT = 300;
    const RIGHT_ALL = 400;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $category;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable = true)
     */
    protected $content;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $readRight;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $editRight;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $isLocked;

    /**
     * @var User $author
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
     * @ORM\JoinColumn()
     */
    protected $author;

    /**
     * @var Organization $organization
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\Organization")
     * @ORM\JoinColumn()
     */
    protected $organization;

    /**
     * @var \DateTime $createdAt
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $createdAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return WikiPage
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return WikiPage
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set category
     *
     * @param string $category
     *
     * @return WikiPage
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return WikiPage
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set readRight
     *
     * @param integer $readRight
     *
     * @return WikiPage
     */
    public function setReadRight($readRight)
    {
        $this->readRight = $readRight;

        return $this;
    }

    /**
     * Get readRight
     *
     * @return integer
     */
    public function getReadRight()
    {
        return $this->readRight;
    }

    /**
     * Set editRight
     *
     * @param integer $editRight
     *
     * @return WikiPage
     */
    public function setEditRight($editRight)
    {
        $this->editRight = $editRight;

        return $this;
    }

    /**
     * Get editRight
     *
     * @return integer
     */
    public function getEditRight()
    {
        return $this->editRight;
    }

    /**
     * Set isLocked
     *
     * @param boolean $isLocked
     *
     * @return WikiPage
     */
    public function setIsLocked($isLocked)
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    /**
     * Get isLocked
     *
     * @return boolean
     */
    public function getIsLocked()
    {
        return $this->isLocked;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return WikiPage
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
     * Set organization
     *
     * @param \Etu\Core\UserBundle\Entity\Organization $organization
     *
     * @return WikiPage
     */
    public function setOrganization(\Etu\Core\UserBundle\Entity\Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return \Etu\Core\UserBundle\Entity\Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set author
     *
     * @param \Etu\Core\UserBundle\Entity\User $author
     *
     * @return WikiPage
     */
    public function setAuthor(\Etu\Core\UserBundle\Entity\User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \Etu\Core\UserBundle\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }
}
