<?php

namespace Etu\Module\CovoitBundle\Model;

use Etu\Core\CoreBundle\Entity\City;
use Symfony\Component\Validator\Constraints as Assert;

class Search
{
    /**
     * @var City
     * @Assert\Type(type="object")
     */
    public $startCity;

    /**
     * @var City
     * @Assert\Type(type="object")
     */
    public $endCity;

    /**
     * @var \DateTime
     * @Assert\Date()
     */
    public $date;

    /**
     * @var boolean
     * @Assert\Type(type="boolean")
     */
    public $dateBeforeAfter;

    /**
     * @var float
     * @Assert\Type(type="float")
     */
    public $priceMax;

    /**
     * @var integer
     * @Assert\Type(type="integer")
     */
    public $placesLeft;

    /**
     * @var \DateTime
     * @Assert\Time()
     */
    public $hourMin;

    /**
     * @var \DateTime
     * @Assert\Time()
     */
    public $hourMax;

    /**
     * @var string
     * @Assert\Type(type="string")
     */
    public $keywords;

    /**
     * @var boolean
     * @Assert\Type(type="boolean")
     */
    public $olds;
}