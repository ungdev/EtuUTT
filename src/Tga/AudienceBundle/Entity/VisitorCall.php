<?php

/*
 * This file is part of the TgaAudienceBundle package.
 *
 * (c) Titouan Galopin <http://titouangalopin.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tga\AudienceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VisitorCall represents a page call by any visitor.
 *
 * @ORM\Table(name="tga_audience_calls")
 * @ORM\Entity(repositoryClass="Tga\AudienceBundle\Entity\VisitorCallRepository")
 */
class VisitorCall
{
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="date", type="datetime")
	 */
	private $date;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="route", type="string", length=255)
	 */
	private $route;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="controller", type="string", length=255)
	 */
	private $controller;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="request_uri", type="string", length=255)
	 */
	private $requestUri;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="referer", type="string", length=255, nullable=true)
	 */
	private $referer;

	/**
	 * @var float
	 *
	 * @ORM\Column(name="time_to_load", type="float")
	 */
	private $timeToLoad;

	/**
	 * @var VisitorSession $session
	 *
	 * @ORM\ManyToOne(targetEntity="VisitorSession")
	 */
	private $session;

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
     * Set date
     *
     * @param \DateTime $date
     * @return VisitorCall
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set route
     *
     * @param string $route
     * @return VisitorCall
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set controller
     *
     * @param string $controller
     * @return VisitorCall
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Get controller
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

	/**
	 * Set requestUri
	 *
	 * @param string $requestUri
	 * @return VisitorCall
	 */
	public function setRequestUri($requestUri)
	{
		$this->requestUri = $requestUri;

		return $this;
	}

	/**
	 * Get requestUri
	 *
	 * @return string
	 */
	public function getRequestUri()
	{
		return $this->requestUri;
	}

    /**
     * Set referer
     *
     * @param string $referer
     * @return VisitorCall
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;

        return $this;
    }

    /**
     * Get referer
     *
     * @return string
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * Set timeToLoad
     *
     * @param float $timeToLoad
     * @return VisitorCall
     */
    public function setTimeToLoad($timeToLoad)
    {
        $this->timeToLoad = $timeToLoad;

        return $this;
    }

    /**
     * Get timeToLoad
     *
     * @return float
     */
    public function getTimeToLoad()
    {
        return $this->timeToLoad;
    }

    /**
     * Set session
     *
     * @param \Tga\AudienceBundle\Entity\VisitorSession $session
     * @return VisitorCall
     */
    public function setSession(\Tga\AudienceBundle\Entity\VisitorSession $session = null)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Get session
     *
     * @return \Tga\AudienceBundle\Entity\VisitorSession
     */
    public function getSession()
    {
        return $this->session;
    }
}
