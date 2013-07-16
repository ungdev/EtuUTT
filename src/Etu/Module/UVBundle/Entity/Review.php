<?php

namespace Etu\Module\UVBundle\Entity;

use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;

use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="etu_uvs_reviews")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Review
{
	const TYPE_PARTIEL = 'partiel';
	const TYPE_MIDTERM = 'midterm';
	const TYPE_FINAL = 'final';

	public static $types = array(
		self::TYPE_PARTIEL,
		self::TYPE_MIDTERM,
		self::TYPE_FINAL,
	);

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
	 * @var UV $uv
	 *
	 * @ORM\ManyToOne(targetEntity="UV")
	 * @ORM\JoinColumn()
	 */
	protected $uv;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=20)
	 */
	protected $type = self::TYPE_PARTIEL;

	/**
	 * @var \DateTime
	 *
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="createdAt", type="datetime")
	 */
	protected $createdAt;

	/**
	 * @var \DateTime
	 *
	 * @Gedmo\Timestampable(on="update")
	 * @ORM\Column(name="updatedAt", type="datetime")
	 */
	protected $updatedAt;

	/**
	 * @var \DateTime $deletedAt
	 *
	 * @ORM\Column(name="deletedAt", type="datetime", nullable = true)
	 */
	protected $deletedAt;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=10)
	 */
	protected $semester;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=50)
	 */
	protected $filename;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $validated;

	/**
	 * Temporary variable to store uploaded file during update
	 *
	 * @var UploadedFile
	 *
	 * @Assert\File(maxSize="10M", mimeTypes={
	 *      "application/pdf",
	 *      "application/x-pdf",
	 *      "image/*"
	 * })
	 */
	public $file;

	/**
	 * @return array
	 */
	public static function availableSemesters()
	{
		$semesters = array();

		for ($i = date('Y') - 10; $i <= date('Y'); $i++) {
			$semesters['P'.$i] = 'P'.$i;
			$semesters['A'.$i] = 'A'.$i;
		}

		return $semesters;
	}

	/**
	 * Upload the file
	 *
	 * @return boolean
	 */
	public function upload()
	{
		if (null === $this->file) {
			return false;
		}

		if (! file_exists(__DIR__.'/../../../../../web/uploads/uvs')) {
			mkdir(__DIR__.'/../../../../../web/uploads/uvs', 0777, true);
		}

		$this->file->move(__DIR__.'/../../../../../web/uploads/uvs');

		return true;
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
     * Set type
     *
     * @param string $type
     * @return Review
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Review
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Review
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return Review
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    
        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime 
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set semester
     *
     * @param string $semester
     * @return Review
     */
    public function setSemester($semester)
    {
        $this->semester = $semester;
    
        return $this;
    }

    /**
     * Get semester
     *
     * @return string 
     */
    public function getSemester()
    {
        return $this->semester;
    }

    /**
     * Set filename
     *
     * @param string $filename
     * @return Review
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    
        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set validated
     *
     * @param boolean $validated
     * @return Review
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;
    
        return $this;
    }

    /**
     * Get validated
     *
     * @return boolean 
     */
    public function getValidated()
    {
        return $this->validated;
    }

    /**
     * Set uv
     *
     * @param \Etu\Module\UVBundle\Entity\UV $uv
     * @return Review
     */
    public function setUv(\Etu\Module\UVBundle\Entity\UV $uv = null)
    {
        $this->uv = $uv;
    
        return $this;
    }

    /**
     * Get uv
     *
     * @return \Etu\Module\UVBundle\Entity\UV 
     */
    public function getUv()
    {
        return $this->uv;
    }
}