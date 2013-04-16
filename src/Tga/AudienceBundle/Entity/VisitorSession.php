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
 * VisitorSession represents a visitor session, which contain many calls.
 *
 * @ORM\Table(name="tga_audience_sessions")
 * @ORM\Entity(repositoryClass="Tga\AudienceBundle\Entity\VisitorSessionRepository")
 */
class VisitorSession
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
	 * @var string
	 *
	 * @ORM\Column(name="ip", type="string", length=255)
	 */
	private $ip;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="browser", type="string", length=255)
	 */
	private $browser;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="browser_version", type="string", length=255)
	 */
	private $browserVersion;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="platform", type="string", length=255)
	 */
	private $platform;

	/**
	 * @var array
	 *
	 * @ORM\Column(name="datas", type="array")
	 */
	private $datas;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="last_visit", type="datetime")
	 */
	private $lastVisit;

	/**
	 * @var VisitorCall $firstCall
	 *
	 * @ORM\OneToMany(targetEntity="VisitorCall", mappedBy="session")
	 */
	private $calls;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->calls = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set ip
     *
     * @param string $ip
     * @return VisitorSession
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set browser
     *
     * @param string $browser
     * @return VisitorSession
     */
    public function setBrowser($browser)
    {
        $this->browser = $browser;

        return $this;
    }

    /**
     * Get browser
     *
     * @return string
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * Set browserVersion
     *
     * @param string $browserVersion
     * @return VisitorSession
     */
    public function setBrowserVersion($browserVersion)
    {
        $this->browserVersion = $browserVersion;

        return $this;
    }

    /**
     * Get browserVersion
     *
     * @return string
     */
    public function getBrowserVersion()
    {
        return $this->browserVersion;
    }

    /**
     * Set platform
     *
     * @param string $platform
     * @return VisitorSession
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Get platform
     *
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set datas
     *
     * @param array $datas
     * @return VisitorSession
     */
    public function setDatas($datas)
    {
        $this->datas = $datas;

        return $this;
    }

    /**
     * Get datas
     *
     * @return array
     */
    public function getDatas()
    {
        return $this->datas;
    }

	/**
	 * Set lastVisit
	 *
	 * @param \DateTime $lastVisit
	 * @return VisitorSession
	 */
	public function setLastVisit($lastVisit)
	{
		$this->lastVisit = $lastVisit;

		return $this;
	}

	/**
	 * Get lastVisit
	 *
	 * @return \DateTime
	 */
	public function getLastVisit()
	{
		return $this->lastVisit;
	}

    /**
     * Add calls
     *
     * @param \Tga\AudienceBundle\Entity\VisitorCall $calls
     * @return VisitorSession
     */
    public function addCall(\Tga\AudienceBundle\Entity\VisitorCall $calls)
    {
        $this->calls[] = $calls;

        return $this;
    }

    /**
     * Remove calls
     *
     * @param \Tga\AudienceBundle\Entity\VisitorCall $calls
     */
    public function removeCall(\Tga\AudienceBundle\Entity\VisitorCall $calls)
    {
        $this->calls->removeElement($calls);
    }

	/**
	 * Get calls
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getCalls()
	{
		return $this->calls;
	}

	/**
	 * @return VisitorCall
	 */
	public function getFirstCall()
	{
		if(! $this->calls[0] instanceof VisitorCall)
			return null;

		return $this->calls[0];
	}

	/**
	 * @return VisitorCall
	 */
	public function getLastCall()
	{
		if(! $this->calls[0] instanceof VisitorCall)
			return null;

		$lastCall = $this->calls[0];

		foreach($this->calls as $call) {
			if($call->getDate()->getTimestamp() > $lastCall->getDate()->getTimestamp()) {
				$lastCall = $call;
			}
		}

		return $lastCall;
	}

	/**
	 * @param $requestUri
	 *
	 * @return bool
	 */
	public function lastPageIs($requestUri)
	{
		if(! $this->calls[0] instanceof VisitorCall)
			return false;

		$lastCall = $this->calls[0];

		foreach($this->calls as $call) {
			if($call->getDate()->getTimestamp() > $lastCall->getDate()->getTimestamp()) {
				$lastCall = $call;
			}
		}

		return $lastCall->getRequestUri() == $requestUri;
	}
}
