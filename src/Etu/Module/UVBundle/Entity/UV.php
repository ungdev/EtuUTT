<?php

namespace Etu\Module\UVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="etu_uvs")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class UV
{
    public const CATEGORY_CS = 'cs';
    public const CATEGORY_TM = 'tm';
    public const CATEGORY_CT = 'ct';
    public const CATEGORY_ME = 'me';
    public const CATEGORY_EC = 'ec';
    public const CATEGORY_ST = 'st';
    public const CATEGORY_OTHER = 'other';

    public static $categories = [
        self::CATEGORY_CS,
        self::CATEGORY_TM,
        self::CATEGORY_CT,
        self::CATEGORY_ME,
        self::CATEGORY_EC,
        self::CATEGORY_ST,
        self::CATEGORY_OTHER,
    ];

    /**
     * @var int
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
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $cm;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $td;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $tp;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $the;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $automne;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $printemps;

    /**
     * @var int
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
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $deletedAt;

    /**
     * @var Review[]
     *
     * @ORM\OneToMany(targetEntity="Review", mappedBy="uv")
     * @ORM\JoinColumn()
     */
    protected $reviews;

    /**
     * @var Comment[]
     *
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="uv")
     * @ORM\JoinColumn()
     */
    protected $comments;

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
     * Set category.
     *
     * @param string $category
     *
     * @return UV
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return UV
     */
    public function setCode($code)
    {
        $this->code = $code;
        $this->slug = StringManipulationExtension::slugify($this->code);

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return UV
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
     * Set name.
     *
     * @param string $name
     *
     * @return UV
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
        return html_entity_decode($this->name);
    }

    /**
     * Set cm.
     *
     * @param int $cm
     *
     * @return UV
     */
    public function setCm($cm)
    {
        $this->cm = $cm;

        return $this;
    }

    /**
     * Get cm.
     *
     * @return int
     */
    public function getCm()
    {
        return $this->cm;
    }

    /**
     * Set td.
     *
     * @param int $td
     *
     * @return UV
     */
    public function setTd($td)
    {
        $this->td = $td;

        return $this;
    }

    /**
     * Get td.
     *
     * @return int
     */
    public function getTd()
    {
        return $this->td;
    }

    /**
     * Set tp.
     *
     * @param int $tp
     *
     * @return UV
     */
    public function setTp($tp)
    {
        $this->tp = $tp;

        return $this;
    }

    /**
     * Get tp.
     *
     * @return int
     */
    public function getTp()
    {
        return $this->tp;
    }

    /**
     * Set the.
     *
     * @param int $the
     *
     * @return UV
     */
    public function setThe($the)
    {
        $this->the = $the;

        return $this;
    }

    /**
     * Get the.
     *
     * @return int
     */
    public function getThe()
    {
        return $this->the;
    }

    /**
     * Set automne.
     *
     * @param bool $automne
     *
     * @return UV
     */
    public function setAutomne($automne)
    {
        $this->automne = $automne;

        return $this;
    }

    /**
     * Get automne.
     *
     * @return bool
     */
    public function getAutomne()
    {
        return $this->automne;
    }

    /**
     * Set printemps.
     *
     * @param bool $printemps
     *
     * @return UV
     */
    public function setPrintemps($printemps)
    {
        $this->printemps = $printemps;

        return $this;
    }

    /**
     * Get printemps.
     *
     * @return bool
     */
    public function getPrintemps()
    {
        return $this->printemps;
    }

    /**
     * Set credits.
     *
     * @param int $credits
     *
     * @return UV
     */
    public function setCredits($credits)
    {
        $this->credits = $credits;

        return $this;
    }

    /**
     * Get credits.
     *
     * @return int
     */
    public function getCredits()
    {
        return $this->credits;
    }

    /**
     * Set objectifs.
     *
     * @param string $objectifs
     *
     * @return UV
     */
    public function setObjectifs($objectifs)
    {
        $this->objectifs = $objectifs;

        return $this;
    }

    /**
     * Get objectifs.
     *
     * @return string
     */
    public function getObjectifs()
    {
        return $this->objectifs;
    }

    /**
     * Set programme.
     *
     * @param string $programme
     *
     * @return UV
     */
    public function setProgramme($programme)
    {
        $this->programme = $programme;

        return $this;
    }

    /**
     * Get programme.
     *
     * @return string
     */
    public function getProgramme()
    {
        return $this->programme;
    }

    /**
     * Set isOld.
     *
     * @param bool $isOld
     *
     * @return UV
     */
    public function setIsOld($isOld)
    {
        $this->isOld = $isOld;

        return $this;
    }

    /**
     * Get isOld.
     *
     * @return bool
     */
    public function getIsOld()
    {
        return $this->isOld;
    }
}
