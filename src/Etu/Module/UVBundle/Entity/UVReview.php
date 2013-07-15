<?php

namespace Etu\Module\UVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * @ORM\Entity
 * @ORM\Table(name="etu_uvs_reviews")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class UVReview
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
	 * @Assert\File(maxSize="10M", mimeTypes=[
	 *      "application/pdf",
	 *      "application/x-pdf",
	 *      "image/*"
	 * ])
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
}
