<?php

namespace Etu\Module\ArgentiqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Photo
 *
 * @ORM\Table(name="etu_argentique_photos")
 * @ORM\Entity
 */
class Photo
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
     * @var string
     *
     * @ORM\Column(name="flickrId", type="string", length=100)
     */
    private $flickrId;

    /**
     * @var PhotoSet $photoSet
     *
     * @ORM\ManyToOne(targetEntity="PhotoSet", inversedBy="photos")
     * @ORM\JoinColumn()
     */
    private $photoSet;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=100)
     */
    private $icon;

    /**
     * @var string
     *
     * @ORM\Column(name="file", type="string", length=100)
     */
    private $file;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * Constructor
     */
    public function __construct()
    {
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
     * @param string $flickrId
     * @return $this
     */
    public function setFlickrId($flickrId)
    {
        $this->flickrId = $flickrId;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlickrId()
    {
        return $this->flickrId;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Photo
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
     * @return Photo
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
     * Set file
     *
     * @param string $file
     * @return Photo
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set photoSet
     *
     * @param \Etu\Module\ArgentiqueBundle\Entity\PhotoSet $photoSet
     * @return Photo
     */
    public function setPhotoSet(\Etu\Module\ArgentiqueBundle\Entity\PhotoSet $photoSet = null)
    {
        $this->photoSet = $photoSet;

        return $this;
    }

    /**
     * Get photoSet
     *
     * @return \Etu\Module\ArgentiqueBundle\Entity\PhotoSet
     */
    public function getPhotoSet()
    {
        return $this->photoSet;
    }
}