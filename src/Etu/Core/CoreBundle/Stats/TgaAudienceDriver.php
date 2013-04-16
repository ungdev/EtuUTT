<?php

namespace Etu\Core\CoreBundle\Stats;

use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * TgaAudienceBundle statistics analysis
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class TgaAudienceDriver
{
	/**
	 * @var Registry
	 */
	protected $doctrine;

	/**
	 * @param Registry $doctrine
	 */
	public function __construct(Registry $doctrine)
	{
		$this->doctrine = $doctrine;
	}

	/**
	 * @return array
	 */
	public function getGlobalStats()
	{
		$uniqueVisitors = array();
		$pagesCalls = array();
		$uniqueVisitorsCount = 0;
		$pagesCallsCount = 0;
		$averageVisitedPages = 0;
		$averageDuration = 0;
		$averageTimeToLoad = 0;
		$callsCount = 0;

		// Generate the stats
		$em = $this->doctrine->getManager();

		$sessions = $em->createQueryBuilder()
			->select('s, c')
			->from('TgaAudienceBundle:VisitorSession', 's')
			->leftJoin('s.calls', 'c')
			->orderBy('s.lastVisit')
			->getQuery()
			->getResult();

		for($i = 1; $i <= 30; $i++) {
			$uniqueVisitors[$i] = 0;
			$pagesCalls[$i] = 0;
		}

		foreach($sessions as $session) {
			if($session->getLastVisit()->format('m') == (new \DateTime())->format('m')) {
				// Unique
				$uniqueVisitors[(int) $session->getLastVisit()->format('d')]++;
				$uniqueVisitorsCount++;

				// All calls
				$pagesCalls[(int) $session->getLastVisit()->format('d')] += count($session->getCalls());
				$pagesCallsCount += count($session->getCalls());

				if(is_object($session->getLastCall()) && is_object($session->getFirstCall())) {
					$averageDuration +=
						$session->getLastCall()->getDate()->getTimestamp()
							- $session->getFirstCall()->getDate()->getTimestamp();
				}

				foreach($session->getCalls() as $call) {
					$averageTimeToLoad += $call->getTimeToLoad();
					$callsCount++;
				}
			}
		}

		if ($uniqueVisitorsCount > 0) {
			$averageVisitedPages = round(($pagesCallsCount - $uniqueVisitorsCount) / $uniqueVisitorsCount, 2);
			$averageDuration = round($averageDuration / $uniqueVisitorsCount, 2);
		}

		if ($callsCount > 0) {
			$averageTimeToLoad = round($averageTimeToLoad / $callsCount, 2) * 1000;
		}

		foreach($uniqueVisitors as $date => $count) {
			unset($uniqueVisitors[$date]);
			$uniqueVisitors[] = array($date, $count);
		}

		foreach($pagesCalls as $date => $count) {
			unset($pagesCalls[$date]);
			$pagesCalls[] = array($date, $count);
		}

		$uniqueVisitors = array_merge(array(array('Date', 'Visiteurs')), $uniqueVisitors);
		$pagesCalls = array_merge(array(array('Date', 'Chargements')), $pagesCalls);

		return array(
			'uniqueVisitors' => json_encode($uniqueVisitors),
			'pagesCalls' => json_encode($pagesCalls),
			'uniqueVisitorsCount' => $uniqueVisitorsCount,
			'pagesCallsCount' => $pagesCallsCount,
			'averageVisitedPages' => $averageVisitedPages,
			'averageDuration' => $averageDuration,
			'averageTimeToLoad' => $averageTimeToLoad
		);
	}

	/**
	 * @return array
	 */
	public function getVisitorsStats()
	{
		// Generate the stats
		$em = $this->doctrine->getManager();

		$sessions = $em->createQueryBuilder()
			->select('s, c')
			->from('TgaAudienceBundle:VisitorSession', 's')
			->leftJoin('s.calls', 'c')
			->orderBy('s.lastVisit')
			->getQuery()
			->getResult();

		$platforms = array();
		$browsers = array();
		$browsersVersions = array();
		$countBrowsersVersions = 0;
		$mostUsedRoutes = array();
		$routes = array();
		$countRoutes = 0;

		foreach($sessions as $session) {
			if($session->getPlatform() != null) {
				if(! isset($platforms[$session->getPlatform()]))
					$platforms[$session->getPlatform()] = 0;

				$platforms[$session->getPlatform()]++;
			}

			if($session->getBrowser() != null) {
				if(! isset($browsers[$session->getBrowser()]))
					$browsers[$session->getBrowser()] = 0;

				if(! isset($browsersVersions[$session->getBrowser().' '.$session->getBrowserVersion()]))
					$browsersVersions[$session->getBrowser().' '.$session->getBrowserVersion()] = 0;

				$browsers[$session->getBrowser()]++;
				$browsersVersions[$session->getBrowser().' '.$session->getBrowserVersion()]++;
				$countBrowsersVersions++;
			}

			foreach($session->getCalls() as $call) {
				if(! isset($routes[$call->getRoute()]))
					$routes[$call->getRoute()] = 0;

				$routes[$call->getRoute()]++;
				$countRoutes++;
			}
		}

		foreach($platforms as $platform => $nb) {
			unset($platforms[$platform]);
			$platforms[] = array($platform, $nb);
		}

		foreach($browsers as $browser => $nb) {
			unset($browsers[$browser]);
			$browsers[] = array($browser, $nb);
		}

		arsort($routes);
		arsort($browsersVersions);

		$i = 1;

		foreach($routes as $route => $nb) {
			if($i > 10)
				break;

			$mostUsedRoutes[$route] = array(
				'place' => $i,
				'route' => $route,
				'nb' => $nb,
				'percentage' => round(($nb / $countRoutes) * 100, 2),
			);

			$i++;
		}

		$i = 1;

		$mostUsedBrowsers = array();

		foreach($browsersVersions as $browsersVersion => $nb) {
			if($i > 10)
				break;

			$mostUsedBrowsers[$browsersVersion] = array(
				'place' => $i,
				'name' => $browsersVersion,
				'nb' => $nb,
				'percentage' => round(($nb / $countBrowsersVersions) * 100, 2),
			);

			$i++;
		}

		$platforms = array_merge(array(array('Platform', 'Count')), $platforms);
		$browsers = array_merge(array(array('Browser', 'Count')), $browsers);

		return array(
			'platforms' => json_encode($platforms),
			'browsers' => json_encode($browsers),
			'mostUsedRoutes' => $mostUsedRoutes,
			'browsersVersions' => $mostUsedBrowsers,
		);
	}

	/**
	 * @param $domain
	 * @return array
	 */
	public function getTrafficStats($domain)
	{
		// Generate the stats
		$em = $this->doctrine->getManager();

		$calls = $em->createQueryBuilder()
			->select('c')
			->from('TgaAudienceBundle:VisitorCall', 'c')
			->orderBy('c.date')
			->getQuery()
			->getResult();

		$sources = array();

		foreach($calls as $call) {
			$referer = $call->getReferer();

			if(! empty($referer)) {
				$referer = parse_url($referer, PHP_URL_HOST);

				if($domain != $referer)
					$sources[] = str_replace('www.', '', $referer);
			}
		}

		$externalSources = array();
		$count = 0;

		foreach($sources as $source) {
			if(! isset($externalSources[$source]))
				$externalSources[$source] = 0;

			$externalSources[$source]++;
			$count++;
		}

		$allExternalSources = $externalSources;

		$i = 0;

		foreach($externalSources as $externalSource => $nb) {
			unset($externalSources[$externalSource]);

			if($i <= 10) {
				$externalSources[] = array($externalSource, $nb);
				$i++;
			}
		}

		arsort($allExternalSources);

		$i = 1;

		foreach($allExternalSources as $source => $value) {
			if($i > 50)
				break;

			$allExternalSources[$source] = array(
				'place' => $i,
				'domain' => $source,
				'nb' => $value,
				'percentage' => round(($value / $count) * 100, 2),
			);

			$i++;
		}

		$externalSources = array_merge(array(array('Source', 'Count')), $externalSources);

		return array(
			'externalSources' => json_encode($externalSources),
			'allExternalSources' => $allExternalSources
		);
	}
}
