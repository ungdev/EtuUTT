<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Titotix
 * Date: 16/04/13
 * Time: 01:16
 * To change this template use File | Settings | File Templates.
 */

namespace Etu\Module\UVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class UV
 * @package Etu\Module\UVBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="etu_uvs")
 */

class UV {

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $code;

    /**
     * @ORM\Column(type="string")
     */
    protected $nom;

    /**
     * @ORM\Column(type="integer")
     */
    protected $cm;

    /**
     * @ORM\Column(type="integer")
     */
    protected $td;

    /**
     * @ORM\Column(type="integer")
     */
    protected $tp;


    /**
     * @ORM\Column(type="integer")
     */
    protected $the;


    /**
     * @ORM\Column(type="boolean")
     */
    protected $automne;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $printemps;

    /**
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


    public function __construct($code) {

        $this->setCode($code);

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
     * Set nom
     *
     * @param string $nom
     * @return UV
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    
        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
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
     * Set objectif
     *
     * @param string $objectif
     * @return UV
     */
    public function setObjectifs($objectifs)
    {
        $this->objectifs = $objectifs;
    
        return $this;
    }

    /**
     * Get objectif
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
}