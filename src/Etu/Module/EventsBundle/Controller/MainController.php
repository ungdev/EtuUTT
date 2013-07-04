<?php

namespace Etu\Module\EventsBundle\Controller;

use CalendR\Period\Month;
use CalendR\Period\Week;
use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Module\EventsBundle\Entity\Answer;
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

		/** @var $answers Answer[] */
		$answers = $em->createQueryBuilder()
			->select('a')
			->from('EtuModuleEventsBundle:Answer', 'a')
			->where('a.event = :id')
			->andWhere('a.answer = :yes OR a.answer = :probably')
			->setParameter('id', $event->getId())
			->setParameter('yes', Answer::ANSWER_YES)
			->setParameter('probably', Answer::ANSWER_PROBABLY)
			->getQuery()
			->getResult();

		$answersYes = array();
		$answersProbably = array();

		foreach ($answers as $answer) {
			if ($answer->getAnswer() == Answer::ANSWER_YES) {
				$answersYes[] = $answer;
			} elseif ($answer->getAnswer() == Answer::ANSWER_PROBABLY) {
				$answersProbably[] = $answer;
			}
		}

		return array(
			'event' => $event,
			'answersYesCount' => count($answersYes),
			'answersProbablyCount' => count($answersProbably),
		);
	}

	/**
	 * @Route("/event/{id}-{slug}/members", name="events_members")
	 * @Template()
	 */
	public function membersAction($id, $slug)
	{
		if (! $this->getUserLayer()->isStudent()) {
			return $this->createAccessDeniedResponse();
		}

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

		/** @var $answers Answer[] */
		$answers = $em->createQueryBuilder()
			->select('a, u')
			->from('EtuModuleEventsBundle:Answer', 'a')
			->leftJoin('a.user', 'u')
			->where('a.event = :id')
			->setParameter('id', $event->getId())
			->getQuery()
			->getResult();

		$answersYes = array();
		$answersProbably = array();
		$answersNo = array();

		foreach ($answers as $answer) {
			if ($answer->getAnswer() == Answer::ANSWER_YES) {
				$answersYes[] = $answer;
			} elseif ($answer->getAnswer() == Answer::ANSWER_PROBABLY) {
				$answersProbably[] = $answer;
			} else {
				$answersNo[] = $answer;
			}
		}

		return array(
			'event' => $event,
			'answersYesCount' => count($answersYes),
			'answersProbablyCount' => count($answersProbably),
			'answersNoCount' => count($answersNo),
			'answersYes' => $answersYes,
			'answersProbably' => $answersProbably,
			'answersNo' => $answersNo,
		);
	}
}
