<?php

namespace Etu\Module\ArgentiqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Photo
 *
 * @ORM\Table(name="etu_argentique_photos")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Photo
{
    /**
     * @var integer
     *
     * @ORM\Column(type="string", length=50)
     * @ORM\Id
     */
    private $id;

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
     * @ORM\Column(type="string", length=100)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $icon;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $file;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $ready = false;

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
        $this->createdAt = new \DateTime();
    }

    /**
     * @ORM\PreRemove
     */
    public function preRemove()
    {
        $directory = __DIR__ . '/../../../../../web/argentique';

        if (file_exists($directory . '/' . $this->getFile())) {
            @unlink($directory . '/' . $this->getFile());
        }

        if (file_exists($directory . '/' . $this->getIcon())) {
            @unlink($directory . '/' . $this->getIcon());
        }
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
     * @param boolean $ready
     * @return $this
     */
    public function setReady($ready)
    {
        $this->ready = $ready;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getReady()
    {
        return $this->ready;
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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