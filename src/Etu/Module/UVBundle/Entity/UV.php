<?php

namespace Etu\Module\UVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;

/**
 * @ORM\Entity
 * @ORM\Table(name="etu_uvs")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class UV
{
	const CATEGORY_CS = 'cs';
	const CATEGORY_TM = 'tm';
	const CATEGORY_CT = 'ct';
	const CATEGORY_ME = 'me';
	const CATEGORY_EC = 'ec';
	const CATEGORY_ST = 'st';
	const CATEGORY_OTHER = 'other';

	public static $categories = array(
		self::CATEGORY_CS,
		self::CATEGORY_TM,
		self::CATEGORY_CT,
		self::CATEGORY_ME,
		self::CATEGORY_EC,
		self::CATEGORY_ST,
		self::CATEGORY_OTHER,
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
	 * @var string
	 *
	 * @ORM\Column(type="string", length=20, nullable = true)
	 */
	protected $category = self::CATEGORY_OTHER;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=10)
	 */
	protected $code;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=10)
	 */
	protected $slug;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=100)
	 */
	protected $name;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    protected $cm;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 */
    protected $td;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 */
    protected $tp;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 */
	protected $the;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $automne;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $printemps;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint")
     */
    protected $credits;

    /**
     * @ORM\Column(type="text")
     */
    protected $objectifs;

	/**
	 * @ORM\Column(type="text")
	 */
	protected $programme;

	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $isOld = false;

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
	 * @var Review[] $reviews
	 *
	 * @ORM\OneToMany(targetEntity="Review", mappedBy="uv")
	 * @ORM\JoinColumn()
	 */
	protected $reviews;

	/**
	 * @var Comment[] $comments
	 *
	 * @ORM\OneToMany(targetEntity="Comment", mappedBy="uv")
	 * @ORM\JoinColumn()
	 */
	protected $comments;

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
     * Set category
     *
     * @param string $category
     * @return UV
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
     * Set code
     *
     * @param string $code
     * @return UV
     */
    public function setCode($code)
    {
        $this->code = $code;
	    $this->slug = StringManipulationExtension::slugify($this->code);

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return UV
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
     * Set name
     *
     * @param string $name
     * @return UV
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return html_entity_decode($this->name);
    }

    /**
     * Set cm
     *
     * @param integer $cm
     * @return UV
     */
    public function setCm($cm)
    {
        $this->cm = $cm;

        return $this;
    }

    /**
     * Get cm
     *
     * @return integer
     */
    public function getCm()
    {
        return $this->cm;
    }

    /**
     * Set td
     *
     * @param integer $td
     * @return UV
     */
    public function setTd($td)
    {
        $this->td = $td;

        return $this;
    }

    /**
     * Get td
     *
     * @return integer
     */
    public function getTd()
    {
        return $this->td;
    }

    /**
     * Set tp
     *
     * @param integer $tp
     * @return UV
     */
    public function setTp($tp)
    {
        $this->tp = $tp;

        return $this;
    }

    /**
     * Get tp
     *
     * @return integer
     */
    public function getTp()
    {
        return $this->tp;
    }

    /**
     * Set the
     *
     * @param integer $the
     * @return UV
     */
    public function setThe($the)
    {
        $this->the = $the;

        return $this;
    }

    /**
     * Get the
     *
     * @return integer
     */
    public function getThe()
    {
        return $this->the;
    }

    /**
     * Set automne
     *
     * @param boolean $automne
     * @return UV
     */
    public function setAutomne($automne)
    {
        $this->automne = $automne;

        return $this;
    }

    /**
     * Get automne
     *
     * @return boolean
     */
    public function getAutomne()
    {
        return $this->automne;
    }

    /**
     * Set printemps
     *
     * @param boolean $printemps
     * @return UV
     */
    public function setPrintemps($printemps)
    {
        $this->printemps = $printemps;

        return $this;
    }

    /**
     * Get printemps
     *
     * @return boolean
     */
    public function getPrintemps()
    {
        return $this->printemps;
    }

    /**
     * Set credits
     *
     * @param integer $credits
     * @return UV
     */
    public function setCredits($credits)
    {
        $this->credits = $credits;

        return $this;
    }

    /**
     * Get credits
     *
     * @return integer
     */
    public function getCredits()
    {
        return $this->credits;
    }

    /**
     * Set objectifs
     *
     * @param string $objectifs
     * @return UV
     */
    public function setObjectifs($objectifs)
    {
        $this->objectifs = $objectifs;

        return $this;
    }

    /**
     * Get objectifs
     *
     * @return string
     */
    public function getObjectifs()
    {
        return $this->objectifs;
    }

    /**
     * Set programme
     *
     * @param string $programme
     * @return UV
     */
    public function setProgramme($programme)
    {
        $this->programme = $programme;

        return $this;
    }

    /**
     * Get programme
     *
     * @return string
     */
    public function getProgramme()
    {
        return $this->programme;
    }

    /**
     * Set isOld
     *
     * @param boolean $isOld
     * @return UV
     */
    public function setIsOld($isOld)
    {
        $this->isOld = $isOld;

        return $this;
    }

    /**
     * Get isOld
     *
     * @return boolean
     */
    public function getIsOld()
    {
        return $this->isOld;
    }
}
