<?php

namespace Etu\Module\EventsBundle\Controller;

use CalendR\Calendar;
use CalendR\Period\Month;
use CalendR\Period\Range;
use CalendR\Period\Week;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Core\UserBundle\Entity\User;
use Etu\Module\EventsBundle\Entity\Answer;
use Etu\Module\EventsBundle\Entity\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
	/**
	 * @Route("/events/{category}", defaults={"category" = "all"}, name="events_index")
	 * @Template()
	 */
	public function indexAction($category = 'all')
	{
		$availableCategories = Event::$categories;
		array_unshift($availableCategories, 'all');

		if (! in_array($category, $availableCategories)) {
			throw $this->createNotFoundException(sprintf('Invalid category "%s"', $category));
		}

		$keys = array_flip($availableCategories);

		return array(
			'availableCategories' => $availableCategories,
			'currentCategory' => $category,
			'currentCategoryId' => $keys[$category],
		);
	}

	/**
	 * @Route(
	 *      "/events/{category}/find",
	 *      defaults={"_format" = "json", "category" = "all"},
	 *      name="events_find",
	 *      options={"expose"=true}
	 * )
	 */
	public function ajaxEventsAction(Request $request, $category = 'all')
	{
		$start = $request->query->get('start');
		$end = $request->query->get('end');

		if (! $start) {
			return new Response(json_encode(array(
				'status' => 'error',
				'message' => '"start" parameter is required',
			)));
		}

		if (! $end) {
			return new Response(json_encode(array(
				'status' => 'error',
				'message' => '"end" parameter is required',
			)));
		}

		$start = \DateTime::createFromFormat('Y-m-d', $start);
		$end = \DateTime::createFromFormat('Y-m-d', $end);

		/** @var Calendar $calendr */
		$calendr = $this->get('calendr');

		/** @var \CalendR\Event\Collection\Basic $events */
		$events = $calendr->getEvents(new Range($start, $end), array(
			'connected' => $this->getUser() instanceof User
		));

		/** @var array $json */
		$json = array();

		/** @var Event $event */
		foreach ($events->all() as $event) {
			if ($category != 'all' && $event->getCategory() != $category) {
				continue;
			}

			$json[] = array(
				'id' => $event->getId(),
				'title' => $event->getTitle(),
				'start' => $event->getBegin()->format('Y-m-d H:i:00'),
				'end' => $event->getEnd()->format('Y-m-d H:i:00'),
				'allDay' => $event->getIsAllDay(),
				'url' => $this->generateUrl('events_view', array(
					'id' => $event->getId(),
					'slug' => StringManipulationExtension::slugify($event->getTitle()),
				))
			);
		}

		return new Response(json_encode($json));
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
			->select('a, u')
			->from('EtuModuleEventsBundle:Answer', 'a')
			->leftJoin('a.user', 'u')
			->where('a.event = :id')
			->setParameter('id', $event->getId())
			->getQuery()
			->getResult();

		$answersYes = array();
		$answersProbably = array();
		$userAnswer = false;

		foreach ($answers as $answer) {
			if ($answer->getAnswer() == Answer::ANSWER_YES) {
				$answersYes[] = $answer;
			} elseif ($answer->getAnswer() == Answer::ANSWER_PROBABLY) {
				$answersProbably[] = $answer;
			}

			if ($this->getUser() && $answer->getUser()->getId() == $this->getUser()->getId()) {
				$userAnswer = $answer;
			}
		}

		if ($event->getBegin() == $event->getEnd() && $event->getBegin()->format('H:i') == '00:00') {
			$useOn = true;
		} else {
			$useOn = false;
		}

		return array(
			'event' => $event,
			'useOn' => $useOn,
			'userAnswer' => $userAnswer,
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

	/**
	 * @Route("/event/{id}/answer/{answer}", name="events_answer", options={"expose" = true})
	 * @Template()
	 */
	public function answerAction($id, $answer)
	{
		if (! in_array($answer, array(Answer::ANSWER_YES, Answer::ANSWER_NO, Answer::ANSWER_PROBABLY))) {
			return new Response(json_encode(array(
				'status' => 'error',
				'message' => 'Invalid answer'
			)), 500);
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
			return new Response(json_encode(array(
				'status' => 'error',
				'message' => 'Event #'.$id.' not found'
			)), 404);
		}

		/** @var $userAnswer Answer */
		$userAnswer = $em->createQueryBuilder()
			->select('a')
			->from('EtuModuleEventsBundle:Answer', 'a')
			->where('a.user = :id')
			->setParameter('id', $this->getUser()->getId())
			->andWhere('a.event = :event')
			->setParameter('event', $event->getId())
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $userAnswer) {
			$userAnswer = new Answer($event, $this->getUser(), $answer);
		} else {
			$userAnswer->setAnswer($answer);
		}

		$em->persist($userAnswer);
		$em->flush();

		if ($answer == Answer::ANSWER_YES || $answer == Answer::ANSWER_PROBABLY) {
			$this->getSubscriptionsManager()->subscribe($this->getUser(), 'event', $event->getId());
		} else {
			$this->getSubscriptionsManager()->unsubscribe($this->getUser(), 'event', $event->getId());
		}

		return new Response(json_encode(array(
			'status' => 'success',
			'message' => 'Ok'
		)), 200);
	}
}
