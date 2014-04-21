<?php

namespace Etu\Module\ArgentiqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Collection
 *
 * @ORM\Table(name="etu_argentique_collections")
 * @ORM\Entity
 */
class Collection
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var PhotoSet[] $sets
     *
     * @ORM\OneToMany(targetEntity="PhotoSet", mappedBy="collection", cascade={"persist", "remove"})
     * @ORM\JoinColumn()
     */
    private $sets;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * Constructor
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->sets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    /**
     * Get sets
     *
     * @return \Doctrine\Common\Collections\Collection|PhotoSet[]
     */
    public function getIcon()
    {
        foreach ($this->sets as $set) {
            foreach ($set->getPhotos() as $photo) {
                return $photo;
            }
        }

        return false;
    }

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
     * @return Collection
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
     * Set description
     *
     * @param string $description
     * @return Collection
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add sets
     *
     * @param \Etu\Module\ArgentiqueBundle\Entity\PhotoSet $sets
     * @return Collection
     */
    public function addSet(\Etu\Module\ArgentiqueBundle\Entity\PhotoSet $sets)
    {
        $this->sets[] = $sets;

        return $this;
    }

    /**
     * Remove sets
     *
     * @param \Etu\Module\ArgentiqueBundle\Entity\PhotoSet $sets
     */
    public function removeSet(\Etu\Module\ArgentiqueBundle\Entity\PhotoSet $sets)
    {
        $this->sets->removeElement($sets);
    }

    /**
     * Get sets
     *
     * @return \Doctrine\Common\Collections\Collection|PhotoSet[]
     */
    public function getSets()
    {
        return $this->sets;
    }

    /**
     * Set sets
     *
     * @param $sets PhotoSet[]
     * @return $this
     */
    public function setSets(array $sets)
    {
        $this->sets = $sets;

        return $this;
    }
}