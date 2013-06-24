<?php

namespace Etu\Module\EventsBundle\Controller;

use CalendR\Period\Month;
use CalendR\Period\Week;
use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Module\EventsBundle\Entity\Event;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
	/**
	 * @Route("/events/month/{month}", name="events_month")
	 * @Template()
	 */
	public function monthAction($month = 'current')
	{
		$currentMonth = array(
			'month' => date('m'),
			'year' => date('Y'),
		);

		if ($month == 'current') {
			$month = $currentMonth;
		} else {
			$month = \DateTime::createFromFormat('m-Y', $month);

			if (! $month) {
				$month = $currentMonth;
			} else {
				$month = array(
					'month' => $month->format('m'),
					'year' => $month->format('Y'),
				);
			}
		}

		/** @var $month Month */
		$month = $this->get('calendr')->getMonth($month['year'], $month['month']);

		$previous = clone $month->getBegin();
		$previous->sub(new \DateInterval('P1M'));

		$next = clone $month->getBegin();
		$next->add(new \DateInterval('P1M'));

		$monthsList = array();

		for ($i = 1; $i <= 5; $i++) {
			$m = clone $month->getBegin();
			$m->sub(new \DateInterval('P'.$i.'M'));

			$monthsList[] = $m;
		}

		$monthsList = array_reverse($monthsList);
		$monthsList[] = $month->getBegin();

		for ($i = 1; $i <= 5; $i++) {
			$m = clone $month->getBegin();
			$m->add(new \DateInterval('P'.$i.'M'));

			$monthsList[] = $m;
		}

		return array(
			'month' => $month,
			'monthsList' => $monthsList,
			'previous' => $previous,
			'next' => $next,
		);
	}

	/**
	 * @Route("/events/{week}", name="events_index")
	 * @Template()
	 */
	public function indexAction($week = 'current')
	{
		$currentWeek = array(
			'week' => date('W'),
			'year' => date('Y'),
		);

		if ($week == 'current' || ! preg_match('/^[0-9]{2}-[0-9]{4}$/', $week)) {
			$week = $currentWeek;
		} else {
			$week = explode('-', $week);
			$week = \DateTime::createFromFormat('z-Y', intval(($week[0] - 1) * 7.0193).'-'.$week[1]);

			if (! $week) {
				$week = $currentWeek;
			} else {
				$week = array(
					'week' => $week->format('W'),
					'year' => $week->format('Y'),
				);
			}
		}

		/** @var $week Week */
		$week = $this->get('calendr')->getWeek($week['year'], $week['week']);

		$previous = clone $week->getBegin();
		$previous->sub(new \DateInterval('P1W'));

		$next = clone $week->getBegin();
		$next->add(new \DateInterval('P1W'));

		$weeksList = array();

		for ($i = 1; $i <= 5; $i++) {
			$w = clone $week->getBegin();
			$w->sub(new \DateInterval('P'.$i.'W'));

			$weeksList[] = $w;
		}

		$weeksList = array_reverse($weeksList);
		$weeksList[] = $week->getBegin();

		for ($i = 1; $i <= 5; $i++) {
			$w = clone $week->getBegin();
			$w->add(new \DateInterval('P'.$i.'W'));

			$weeksList[] = $w;
		}

		return array(
			'week' => $week,
			'weeksList' => $weeksList,
			'previous' => $previous,
			'next' => $next,
		);
	}

	/**
	 * @Route("/event/{id}-{slug}", name="events_view")
	 * @Template()
	 */
	public function viewAction($id, $slug)
	{
		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $event Event */
		$event = $em->createQueryBuilder()
			->select('e, o')
			->from('EtuModuleEventsBundle:Event', 'e')
			->leftJoin('e.orga', 'o')
			->where('e.uid = :id')
			->setParameter('id', $id)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $event) {
			throw $this->createNotFoundException('Event #'.$id.' not found');
		}

		if (StringManipulationExtension::slugify($event->getTitle()) != $slug) {
			return $this->redirect($this->generateUrl('events_view', array(
				'id' => $id, 'slug' => StringManipulationExtension::slugify($event->getTitle())
			)), 301);
		}

		return array();
	}
}
