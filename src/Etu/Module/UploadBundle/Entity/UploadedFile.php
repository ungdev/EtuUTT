<?php

namespace Etu\Module\UploadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Etu\Core\UserBundle\Entity\Organization;
use Imagine\Gd\Image;

/**
 * @ORM\Entity
 * @ORM\Table(name="etu_upload_files")
 */
class UploadedFile
{
    /* List of rights sorted by most important to less */
    const RIGHT = [
        'ADMIN' => 0,
        'ORGA_ADMIN' => 100,
        'ORGA_MEMBER' => 200,
        'STUDENT' => 300,
        'ALL' => 400,
    ];

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30)
     * @Assert\NotBlank()
     * @Assert\Length(min = "3", max = "30")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10)
     * @Assert\Length(min = "1", max = "10")
     */
    protected $extension;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $readRight;

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
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Assert\Length(min = "3")
     */
    protected $description;

    /**
     * Temporary variable to store uploaded file.
     *
     * @var file
     *
     * @Assert\File(maxSize = "5M",
     *     mimeTypes = {"application/pdf", "text/plain", "text/html", "application/zip", "video/webm", "audio/webm", "audio/mpeg", "audio/mp3", "image/jpeg", "image/png", "image/gif"},
     * )
     */
    public $file;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->readRight = self::RIGHT['ALL'];
        $this->description = '';
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
     * Set name.
     *
     * @param string $name
     *
     * @return UploadedFile
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set extension.
     *
     * @param string $extension
     *
     * @return UploadedFile
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension.
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set namespace.
     *
     * @param string $namespace
     *
     * @return UploadedFile
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Get namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set readRight.
     *
     * @param int $readRight
     *
     * @return UploadedFile
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
     * Set validated.
     *
     * @param bool $validated
     *
     * @return UploadedFile
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
    public function getValidated()
    {
        return $this->validated;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return UploadedFile
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
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return User
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return UploadedFile
     */
    public function setDescription($description)
    {
        $this->description = $description ?? '';

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set author.
     *
     * @param \Etu\Core\UserBundle\Entity\User $author
     *
     * @return UploadedFile
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
     * Set organization.
     *
     * @param \Etu\Core\UserBundle\Entity\Organization $organization
     *
     * @return UploadedFile
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
     * Set file.
     *
     * @param string $file
     *
     * @return UploadedFile
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }
}
