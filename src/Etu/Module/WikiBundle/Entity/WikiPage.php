<?php

namespace Etu\Module\WikiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="etu_wiki_pages")
 * @ORM\MappedSuperclass
 */
class WikiPage
{
    /* List of rights sorted by most important to less */
    public const RIGHT = [
        'ADMIN' => 0,
        'ORGA_ADMIN' => 100,
        'ORGA_MEMBER' => 200,
        'STUDENT' => 300,
        'ALL' => 400,
    ];

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
     * @Assert\NotBlank()
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
     * @Assert\NotBlank()
     * @ORM\Column(type="text", nullable = true)
     */
    protected $content;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $readRight;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $editRight;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
     * @ORM\JoinColumn()
     */
    protected $author;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $validated;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\Organization")
     * @ORM\JoinColumn()
     */
    protected $organization;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $createdAt;

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return WikiPage
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title.
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
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug.
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
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set content.
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
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set readRight.
     *
     * @param int $readRight
     *
     * @return WikiPage
     */
    public function setReadRight($readRight)
    {
        $this->readRight = $readRight;

        return $this;
    }

    /**
     * Get readRight.
     *
     * @return int
     */
    public function getReadRight()
    {
        return $this->readRight;
    }

    /**
     * Set editRight.
     *
     * @param int $editRight
     *
     * @return WikiPage
     */
    public function setEditRight($editRight)
    {
        $this->editRight = $editRight;

        return $this;
    }

    /**
     * Get editRight.
     *
     * @return int
     */
    public function getEditRight()
    {
        return $this->editRight;
    }

    /**
     * Set createdAt.
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
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set organization.
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
     * Get organization.
     *
     * @return \Etu\Core\UserBundle\Entity\Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set author.
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
     * Get author.
     *
     * @return \Etu\Core\UserBundle\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set validated.
     *
     * @param bool $validated
     *
     * @return WikiPage
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;

        return $this;
    }

    /**
     * Get validated.
     *
     * @return bool
     */
    public function isValidated()
    {
        return $this->validated;
    }

    /**
     * Remove title and content to delete the page.
     *
     * @return string
     */
    public function delete()
    {
        $this->content = '';
        $this->title = '';
    }

    /**
     * Check if this page is deleted.
     *
     * @return string
     */
    public function isDeleted()
    {
        return $this->content == '' && $this->title = '';
    }
}
