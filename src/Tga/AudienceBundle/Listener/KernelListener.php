<?php

/*
 * This file is part of the TgaAudienceBundle package.
 *
 * (c) Titouan Galopin <http://titouangalopin.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tga\AudienceBundle\Listener;

use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Kernel listener, to store request and load datas for each call.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class KernelListener
{
	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Kernel
	 */
	private $kernel;

	/**
	 * @var Registry
	 */
	private $dotrine;

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @var array
	 */
	private $sessionData;

	/**
	 * @var float
	 */
	private $startTime;

	public function __construct(Container $container)
	{
		$this->request = $container->get('request');
		$this->kernel = $container->get('kernel');
		$this->doctrine = $container->get('doctrine');

		$this->config = array(
			'sessionDuration' => $container->getParameter('tga_audience.session_duration'),
			'disabledRoutes' => $container->getParameter('tga_audience.disabled_routes'),
			'environnements' => $container->getParameter('tga_audience.environnements'),
		);
	}

	/**
	 * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
	 */
	public function onKernelRequest(GetResponseEvent $event)
	{
		$this->startTime = microtime(true);
		$this->sessionData = $this->request->getSession()->all();
	}

	/**
	 * @param PostResponseEvent $event
	 */
	public function onKernelTerminate(PostResponseEvent $event)
	{
		if(! in_array($this->kernel->getEnvironment(), $this->config['environnements']))
			return;

		if(in_array($this->request->get('_route'), $this->config['disabledRoutes']))
			return;

		if(strpos($this->request->get('_controller'), 'Tga\AudienceBundle') !== false)
			return;

		$infos = $this->getBrowser($_SERVER['HTTP_USER_AGENT']);

		// Get the session and update it if required
		$em = $this->doctrine->getManager();

		$session = $em->createQueryBuilder()
			->select('s, c')
			->from('TgaAudienceBundle:VisitorSession', 's')
			->leftJoin('s.calls', 'c')
			->where('s.ip = :ip')
			->andWhere('s.lastVisit > :invalidateTime')
			->setParameter('ip', $this->request->getClientIp())
			->setParameter('invalidateTime', time() - $this->config['sessionDuration'])
			->getQuery()
			->getOneOrNullResult();

		if(! $session) {
			$session = new \Tga\AudienceBundle\Entity\VisitorSession();

			$infos = $this->getBrowser($_SERVER['HTTP_USER_AGENT']);

			$session->setBrowser($infos['browser'])
				->setBrowserVersion($infos['version'])
				->setPlatform($infos['platform'])
				->setIp($this->request->getClientIp())
				->setDatas($this->sessionData);
		}

		$session->setLastVisit(new \DateTime());
		$em->persist($session);

		if(! $session->lastPageIs($this->request->getRequestUri())) {
			$call = new \Tga\AudienceBundle\Entity\VisitorCall();

			$call->setSession($session)
				->setDate(new \DateTime())
				->setController($this->request->get('_controller'))
				->setRoute($this->request->get('_route'))
				->setRequestUri($this->request->getRequestUri())
				->setReferer(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null)
				->setTimeToLoad(microtime(true) - $this->startTime);

			$em->persist($call);
		}

		$em->flush();
	}

	/**
	 * Find the browser
	 *
	 * @param $u_agent
	 *
	 * @return array
	 *
	 * @author <ruudrp@live.nl>
	 */
	private function getBrowser($u_agent)
	{
		$bname = 'Unknown';
		$platform = 'Unknown';
		$version = 'Unknown';

		// First get the platform
		if(preg_match('/linux/i', $u_agent)) {
			$platform = 'Linux';
		}
		elseif(preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'Mac';
		}
		elseif(preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'Windows';
		}

		// Next get the name of the useragent yes seperately and for good reason
		if(preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
			$bname = 'Internet Explorer';
			$ub = "MSIE";
		}
		elseif(preg_match('/Firefox/i', $u_agent)) {
			$bname = 'Mozilla Firefox';
			$ub = "Firefox";
		}
		elseif(preg_match('/Chrome/i', $u_agent)) {
			$bname = 'Google Chrome';
			$ub = "Chrome";
		}
		elseif(preg_match('/Safari/i', $u_agent)) {
			$bname = 'Apple Safari';
			$ub = "Safari";
		}
		elseif(preg_match('/Opera/i', $u_agent)) {
			$bname = 'Opera';
			$ub = "Opera";
		}
		elseif(preg_match('/Netscape/i', $u_agent)) {
			$bname = 'Netscape';
			$ub = "Netscape";
		}

		// Get the correct version number
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>'. join('|', $known) .')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

		preg_match_all($pattern, $u_agent, $matches);

		$i = count($matches['browser']);

		if($i != 1) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($u_agent, 'Version') < strripos($u_agent, $ub)) {
				$version = $matches['version'][0];
			}
			else {
				$version = $matches['version'][1];
			}
		}
		else {
			$version = $matches['version'][0];
		}

		// check if we have a number
		if(empty($version)) {
			$version = 'Unknown';
		}

		return array(
			'browser'       => $bname,
			'version'       => $version,
			'platform'      => $platform
		);
	}
}
