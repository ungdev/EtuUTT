<?php

namespace Etu\Module\UVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="etu_uvs")
 */
class UV
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=10)
	 */
	protected $code;

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
     * @ORM\Column(type="array")
     */
    protected $objectifs;

    /**
     * @ORM\Column(type="array")
     */
    protected $programme;

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
     * Set code
     *
     * @param string $code
     * @return UV
     */
    public function setCode($code)
    {
        $this->code = $code;

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
        return $this->name;
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
	 * @param array $objectifs
	 * @return UV
	 */
	public function setObjectifs($objectifs)
	{
		$this->objectifs = $objectifs;

		return $this;
	}

	/**
	 * Add objectif
	 *
	 * @param string $objectif
	 * @return UV
	 */
	public function addObjectif($objectif)
	{
		$this->objectifs[] = $objectif;

		return $this;
	}

    /**
     * Get objectifs
     *
     * @return array
     */
    public function getObjectifs()
    {
        return $this->objectifs;
    }

	/**
	 * Set programme
	 *
	 * @param array $programme
	 * @return UV
	 */
	public function setProgramme($programme)
	{
		$this->programme = $programme;

		return $this;
	}

	/**
	 * Add programme line
	 *
	 * @param string $programme
	 * @return UV
	 */
	public function addProgrammeLine($programme)
	{
		$this->programme[] = $programme;

		return $this;
	}

    /**
     * Get programme
     *
     * @return array
     */
    public function getProgramme()
    {
        return $this->programme;
    }
}
