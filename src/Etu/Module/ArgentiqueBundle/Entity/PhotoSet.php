<?php

namespace Etu\Module\ArgentiqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PhotoSet
 *
 * @ORM\Table(name="etu_argentique_photos_sets")
 * @ORM\Entity
 */
class PhotoSet
{
    /**
     * @var integer
     *
     * @ORM\Column(type="string", length=50)
     * @ORM\Id
     */
    private $id;

    /**
     * @var Collection $collection
     *
     * @ORM\ManyToOne(targetEntity="Collection", inversedBy="sets")
     * @ORM\JoinColumn()
     */
    private $collection;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=100, nullable=true)
     */
    private $icon;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var Photo[] $photos
     *
     * @ORM\OneToMany(targetEntity="Photo", mappedBy="photoSet", cascade={"persist", "remove"})
     * @ORM\JoinColumn()
     */
    private $photos;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var Photo[]
     */
    public $importingPhotos = [];

    /**
     * Constructor
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->photos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->createdAt = new \DateTime();
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
     * @return PhotoSet
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
     * Set icon
     *
     * @param string $icon
     * @return PhotoSet
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return PhotoSet
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
     * Set collection
     *
     * @param \Etu\Module\ArgentiqueBundle\Entity\Collection $collection
     * @return PhotoSet
     */
    public function setCollection(\Etu\Module\ArgentiqueBundle\Entity\Collection $collection = null)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Get collection
     *
     * @return \Etu\Module\ArgentiqueBundle\Entity\Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Add photos
     *
     * @param \Etu\Module\ArgentiqueBundle\Entity\Photo $photos
     * @return PhotoSet
     */
    public function addPhoto(\Etu\Module\ArgentiqueBundle\Entity\Photo $photos)
    {
        $this->photos[] = $photos;

        return $this;
    }

    /**
     * Remove photos
     *
     * @param \Etu\Module\ArgentiqueBundle\Entity\Photo $photos
     */
    public function removePhoto(\Etu\Module\ArgentiqueBundle\Entity\Photo $photos)
    {
        $this->photos->removeElement($photos);
    }

    /**
     * Get photos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhotos()
    {
        return $this->photos;
    }
}