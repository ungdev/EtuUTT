<?php

namespace Etu\Module\UVBundle\Entity;

use Etu\Core\UserBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="Etu\Module\UVBundle\Entity\Repository\ReviewRepository")
 * @ORM\Table(name="etu_uvs_reviews")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Review
{
    const TYPE_PARTIEL = 'partiel';
    const TYPE_PARTIEL_1 = 'partiel_1';
    const TYPE_PARTIEL_2 = 'partiel_2';
    const TYPE_DM = 'dm';
	const TYPE_MIDTERM = 'midterm';
	const TYPE_FINAL = 'final';

	public static $types = array(
        self::TYPE_PARTIEL => 'uvs.reviews.partiel',
        self::TYPE_PARTIEL_1 => 'uvs.reviews.partiel_1',
        self::TYPE_PARTIEL_2 => 'uvs.reviews.partiel_2',
        self::TYPE_DM => 'uvs.reviews.dm',
		self::TYPE_MIDTERM => 'uvs.reviews.midterm',
		self::TYPE_FINAL => 'uvs.reviews.final',
	);

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
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
	 * @var User $sender
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
	 * @ORM\JoinColumn()
	 */
	protected $sender;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=20)
	 *
	 * @Assert\NotBlank()
	 */
	protected $type = self::TYPE_PARTIEL;

	/**
	 * @var \DateTime
	 *
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(type="datetime")
	 */
	protected $createdAt;

	/**
	 * @var \DateTime
	 *
	 * @Gedmo\Timestampable(on="update")
	 * @ORM\Column(type="datetime")
	 */
	protected $updatedAt;

	/**
	 * @var \DateTime $deletedAt
	 *
	 * @ORM\Column(type="datetime", nullable = true)
	 */
	protected $deletedAt;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=10, nullable = true)
	 *
	 * @Assert\NotBlank()
	 */
	protected $semester;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=50, nullable = true)
	 */
	protected $filename;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $validated = false;

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

		for ($i = date('Y'); $i >= date('Y') - 15; $i--) {
			$semesters['P'.$i] = 'P'.$i;
			$semesters['A'.$i] = 'A'.$i;
		}

		if (User::currentSemester() == 'P'.date('Y')) {
			unset($semesters['A'.date('Y')]);
		}

		return $semesters;
	}

	/**
	 * @deprecated Deprecated since version 10.0 Bêta1, to be removed in 10.1. Use
	 *             {@link User::currentSemester()} instead.
	 * @return string
	 */
	public static function currentSemester()
	{
		return User::currentSemester();
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

		if ($this->type == self::TYPE_FINAL) {
			$name = 'final';
		} elseif ($this->type == self::TYPE_MIDTERM) {
            $name = 'median';
        } elseif ($this->type == self::TYPE_PARTIEL_1) {
            $name = 'partiel-1';
        } elseif ($this->type == self::TYPE_PARTIEL_2) {
            $name = 'partiel-2';
        } elseif ($this->type == self::TYPE_DM) {
            $name = 'dm';
        } else {
			$name = 'partiel';
		}

		$name .= '-'.$this->semester;
		$name .= '-'.$this->getUv()->getSlug();
		$name .= '-'.substr(md5(uniqid(true)), 0, 3);

		$ext = $this->file->guessExtension();

		if (! $ext) {
			$ext = $this->file->getClientOriginalExtension();
		}

		$name .= '.'.$ext;

		$this->file->move(__DIR__.'/../../../../../web/uploads/uvs', $name);

		$this->filename = $name;

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
     * Get type
     *
     * @return string
     */
    public function getTypeTransKey()
    {
        return self::$types[$this->type];
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
     * @param UV $uv
     * @return Review
     */
    public function setUv(UV $uv = null)
    {
        $this->uv = $uv;

        return $this;
    }

    /**
     * Get uv
     *
     * @return UV
     */
    public function getUv()
    {
        return $this->uv;
    }

	/**
	 * Set sender
	 *
	 * @param User $sender
	 * @return Review
	 */
	public function setSender(User $sender = null)
	{
		$this->sender = $sender;

		return $this;
	}

	/**
	 * Get sender
	 *
	 * @return User
	 */
	public function getSender()
	{
		return $this->sender;
	}
}
