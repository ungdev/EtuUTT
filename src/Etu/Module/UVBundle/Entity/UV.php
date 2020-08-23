<?php

namespace Etu\Module\UVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="etu_uvs")
 */
class UV
{
    public const CATEGORY_CS = 'cs';
    public const CATEGORY_TM = 'tm';
    public const CATEGORY_CT = 'ct';
    public const CATEGORY_ME = 'me';
    public const CATEGORY_EC = 'ec';
    public const CATEGORY_ST = 'st';
    public const CATEGORY_MASTER = 'master';

    public static $categories = [
        self::CATEGORY_CS,
        self::CATEGORY_TM,
        self::CATEGORY_CT,
        self::CATEGORY_ME,
        self::CATEGORY_EC,
        self::CATEGORY_ST,
        self::CATEGORY_MASTER,
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
    protected $category = self::CATEGORY_CS;

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
     * @ORM\Column(type="string", length=150)
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
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $projet;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $stage;

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
    protected $diplomes;

    /**
     * @ORM\Column(type="text")
     */
    protected $mineurs;

    /**
     * @ORM\Column(type="text")
     */
    protected $antecedents;

    /**
     * @ORM\Column(type="text")
     */
    protected $languages;

    /**
     * @ORM\Column(type="text")
     */
    protected $commentaire;

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
     * Set projet.
     *
     * @param int $projet
     *
     * @return UV
     */
    public function setProjet($projet)
    {
        $this->projet = $projet;

        return $this;
    }

    /**
     * Get projet.
     *
     * @return int
     */
    public function getProjet()
    {
        return $this->projet;
    }

    /**
     * Set stage.
     *
     * @param int $stage
     *
     * @return UV
     */
    public function setStage($stage)
    {
        $this->stage = $stage;

        return $this;
    }

    /**
     * Get stage.
     *
     * @return int
     */
    public function getStage()
    {
        return $this->stage;
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
     * Set diplomes.
     *
     * @param string $diplomes
     *
     * @return UV
     */
    public function setDiplomes($diplomes)
    {
        $this->diplomes = $diplomes;

        return $this;
    }

    /**
     * Get diplomes.
     *
     * @return string
     */
    public function getDiplomes()
    {
        return $this->diplomes;
    }

    /**
     * Set mineurs.
     *
     * @param string $mineurs
     *
     * @return UV
     */
    public function setMineurs($mineurs)
    {
        $this->mineurs = $mineurs;

        return $this;
    }

    /**
     * Get mineurs.
     *
     * @return string
     */
    public function getMineurs()
    {
        return $this->mineurs;
    }

    /**
     * Set antecedents.
     *
     * @param string $antecedents
     *
     * @return UV
     */
    public function setAntecedents($antecedents)
    {
        $this->antecedents = $antecedents;

        return $this;
    }

    /**
     * Get antecedents.
     *
     * @return string
     */
    public function getAntecedents()
    {
        return $this->antecedents;
    }

    /**
     * Set languages.
     *
     * @param string $languages
     *
     * @return UV
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;

        return $this;
    }

    /**
     * Get languages.
     *
     * @return string
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * Set commentaire.
     *
     * @param string $commentaire
     *
     * @return UV
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire.
     *
     * @return string
     */
    public function getCommentaire()
    {
        return $this->commentaire;
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
