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
use Symfony\Component\Validator\Constraints\DateTime;

class MainController extends Controller
{
	/**
	 * @Route("/events/{month}/{category}", defaults={"category" = "all"}, name="events_index")
	 * @Template()
	 */
	public function indexAction($category = 'all', $month = 'current')
	{
		$availableCategories = Event::$categories;

		array_unshift($availableCategories, 'all');

		if (! in_array($category, $availableCategories)) {
			throw $this->createNotFoundException(sprintf('Invalid category "%s"', $category));
		}

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

		$keys = array_flip($availableCategories);

		return array(
			'month' => $month,
			'monthsList' => $monthsList,
			'previous' => $previous,
			'next' => $next,
			'availableCategories' => $availableCategories,
			'currentCategory' => $category,
			'currentCategoryId' => $keys[$category],
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

		return array(
			'event' => $event
		);
	}
}
