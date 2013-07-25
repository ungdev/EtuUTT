<?php

namespace Etu\Module\EventsBundle\Controller;

use CalendR\Calendar;
use CalendR\Period\Month;
use CalendR\Period\Range;
use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Symfony\Component\HttpFoundation\Request;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Core\CoreBundle\Util\RedactorJsEscaper;
use Etu\Core\UserBundle\Entity\Member;
use Etu\Module\EventsBundle\Entity\Event;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

class MembershipsController extends Controller
{
	/**
	 * @Route(
	 *      "/user/membership/{login}/events",
	 *      defaults={"day" = "current"},
	 *      name="memberships_orga_events"
	 * )
	 * @Template()
	 */
	public function eventsAction($login)
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $memberships Member[] */
		$memberships = $em->createQueryBuilder()
			->select('m, o')
			->from('EtuUserBundle:Member', 'm')
			->leftJoin('m.organization', 'o')
			->andWhere('m.user = :user')
			->setParameter('user', $this->getUser()->getId())
			->orderBy('m.role', 'DESC')
			->addOrderBy('o.name', 'ASC')
			->getQuery()
			->getResult();

		$membership = null;

		foreach ($memberships as $m) {
			if ($m->getOrganization()->getLogin() == $login) {
				$membership = $m;
				break;
			}
		}

		if (! $membership) {
			throw $this->createNotFoundException('Membership or organization not found for login '.$login);
		}

		if (! $membership->hasPermission('events')) {
			return $this->createAccessDeniedResponse();
		}

		$orga = $membership->getOrganization();

		$event = new Event(null, new \DateTime(), new \DateTime());
		$event->setOrga($orga);

		$categories = array();

		foreach (Event::$categories as $category) {
			$categories[$category] = 'events.categories.'.$category;
		}

		$form = $this->createFormBuilder($event)
			->add('title')
			->add('category', 'choice', array('choices' => $categories))
			->add('location', 'textarea')
			->getForm();

		return array(
			'memberships' => $memberships,
			'membership' => $membership,
			'orga' => $orga,
			'form' => $form->createView()
		);
	}

	/**
	 * @Route(
	 *      "/user/membership/{login}/events/find",
	 *      defaults={"_format"="json"},
	 *      name="memberships_orga_events_find",
	 *      options={"expose"=true}
	 * )
	 * @Template()
	 */
	public function ajaxEventsAction(Request $request, $login)
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $memberships Member[] */
		$memberships = $em->createQueryBuilder()
			->select('m, o')
			->from('EtuUserBundle:Member', 'm')
			->leftJoin('m.organization', 'o')
			->andWhere('m.user = :user')
			->setParameter('user', $this->getUser()->getId())
			->orderBy('m.role', 'DESC')
			->addOrderBy('o.name', 'ASC')
			->getQuery()
			->getResult();

		$membership = null;

		foreach ($memberships as $m) {
			if ($m->getOrganization()->getLogin() == $login) {
				$membership = $m;
				break;
			}
		}

		if (! $membership) {
			throw $this->createNotFoundException('Membership or organization not found for login '.$login);
		}

		if (! $membership->hasPermission('events')) {
			return $this->createAccessDeniedResponse();
		}

		$orga = $membership->getOrganization();

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

		$start = \DateTime::createFromFormat('U', $start);
		$end = \DateTime::createFromFormat('U', $end);

		/** @var Calendar $calendr */
		$calendr = $this->get('calendr');

		/** @var \CalendR\Event\Collection\Basic $events */
		$events = $calendr->getEvents(new Range($start, $end));

		/** @var array $json */
		$json = array();

		/** @var Event $event */
		foreach ($events->all() as $event) {
			if ($event->getOrga()->getId() != $orga->getId()) {
				continue;
			}

			$json[] = array(
				'id' => $event->getId(),
				'title' => $event->getTitle(),
				'start' => $event->getBegin()->format('Y-m-d H:i:00'),
				'end' => $event->getEnd()->format('Y-m-d H:i:00'),
				'allDay' => $event->getIsAllDay(),
				'url' => $this->generateUrl('memberships_orga_events_edit', array(
					'login' => $orga->getLogin(),
					'id' => $event->getId(),
					'slug' => StringManipulationExtension::slugify($event->getTitle()),
				))
			);
		}

		return new Response(json_encode($json));
	}
	/**
	 * @Route(
	 *      "/user/membership/{login}/events/create",
	 *      name="memberships_orga_events_create",
	 *      options={"expose"=true}
	 * )
	 * @Template()
	 */
	public function createAction(Request $request, $login)
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $memberships Member[] */
		$memberships = $em->createQueryBuilder()
			->select('m, o')
			->from('EtuUserBundle:Member', 'm')
			->leftJoin('m.organization', 'o')
			->andWhere('m.user = :user')
			->setParameter('user', $this->getUser()->getId())
			->orderBy('m.role', 'DESC')
			->addOrderBy('o.name', 'ASC')
			->getQuery()
			->getResult();

		$membership = null;

		foreach ($memberships as $m) {
			if ($m->getOrganization()->getLogin() == $login) {
				$membership = $m;
				break;
			}
		}

		if (! $membership) {
			throw $this->createNotFoundException('Membership or organization not found for login '.$login);
		}

		if (! $membership->hasPermission('events')) {
			return $this->createAccessDeniedResponse();
		}

		$start = $request->query->get('s');
		$end = $request->query->get('e');
		$allDay = ($request->query->get('a') == 'true') ? true : false;

		if (! $start || ! $end) {
			throw $this->createNotFoundException();
		}

		$orga = $membership->getOrganization();

		$event = new Event(
			null,
			\DateTime::createFromFormat('d-m-Y--H-i', $start),
			\DateTime::createFromFormat('d-m-Y--H-i', $end)
		);
		$event->setOrga($orga)
			->setIsAllDay($allDay);

		$categories = array();

		foreach (Event::$categories as $category) {
			$categories[$category] = 'events.categories.'.$category;
		}

		$form = $this->createFormBuilder($event)
			->add('title')
			->add('category', 'choice', array('choices' => $categories))
			->add('file', 'file')
			->add('location', 'textarea')
			->add('description', 'redactor')
			->getForm();

		if ($request->getMethod() == 'POST' && $form->submit($request)->isValid()) {
			$event->setDescription(RedactorJsEscaper::escape($event->getDescription()));

			$event->upload();

			$em->persist($event);
			$em->flush();

			$entity = array(
				'id' => $event->getId(),
				'title' => $event->getTitle(),
				'category' => $categories[$event->getCategory()],
				'orga' => array(
					'id' => $event->getOrga()->getId(),
					'name' => $event->getOrga()->getName(),
				)
			);

			// Send notifications to subscribers of all eventts
			$notif = new Notification();

			$notif
				->setModule($this->getCurrentBundle()->getIdentifier())
				->setHelper('event_created_all')
				->setAuthorId($this->getUser()->getId())
				->setEntityType('event')
				->setEntityId(0)
				->addEntity($entity);

			$this->getNotificationsSender()->send($notif);

			// Send notifications to subscribers of specific category
			$notif = new Notification();

			$availableCategories = Event::$categories;
			array_unshift($availableCategories, 'all');
			$keys = array_flip($availableCategories);

			$notif
				->setModule($this->getCurrentBundle()->getIdentifier())
				->setHelper('event_created_category')
				->setAuthorId($this->getUser()->getId())
				->setEntityType('event-category')
				->setEntityId($keys[$event->getCategory()])
				->addEntity($entity);

			$this->getNotificationsSender()->send($notif);

			// Confirmation
			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'events.memberships.create.confirm'
			));

			return $this->redirect($this->generateUrl('memberships_orga_events', array(
				'login' => $login
			)));
		}

		return array(
			'memberships' => $memberships,
			'membership' => $membership,
			'orga' => $orga,
			'form' => $form->createView(),
			'event' => $event
		);
	}

	/**
	 * @Route(
	 *      "/user/membership/{login}/event/{id}-{slug}",
	 *      name="memberships_orga_events_edit"
	 * )
	 * @Template()
	 */
	public function editAction(Request $request, $login, $id, $slug)
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $memberships Member[] */
		$memberships = $em->createQueryBuilder()
			->select('m, o')
			->from('EtuUserBundle:Member', 'm')
			->leftJoin('m.organization', 'o')
			->andWhere('m.user = :user')
			->setParameter('user', $this->getUser()->getId())
			->orderBy('m.role', 'DESC')
			->addOrderBy('o.name', 'ASC')
			->getQuery()
			->getResult();

		$membership = null;

		foreach ($memberships as $m) {
			if ($m->getOrganization()->getLogin() == $login) {
				$membership = $m;
				break;
			}
		}

		if (! $membership) {
			throw $this->createNotFoundException('Membership or organization not found for login '.$login);
		}

		if (! $membership->hasPermission('events')) {
			return $this->createAccessDeniedResponse();
		}

		$orga = $membership->getOrganization();

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

		if ($event->getOrga()->getId() != $orga->getId()) {
			return $this->createAccessDeniedResponse();
		}

		$categories = array();

		foreach (Event::$categories as $category) {
			$categories[$category] = 'events.categories.'.$category;
		}

		$oldEvent = clone $event;

		$form = $this->createFormBuilder($event)
			->add('title')
			->add('category', 'choice', array('choices' => $categories))
			->add('location', 'textarea')
			->add('description', 'redactor')
			->getForm();

		if ($request->getMethod() == 'POST' && $form->submit($request)->isValid()) {
			$event->setDescription(RedactorJsEscaper::escape($event->getDescription()));

			$em->persist($event);
			$em->flush();

			// Send smart notification
			$elementsToChange = array(
				'begin' => false,
				'beginDate' => false,
				'beginHour' => false,
				'end' => false,
				'endDate' => false,
				'endHour' => false,
				'location' => false,
				'other' => false,
			);

			if ($oldEvent->getBegin() != $event->getBegin()) {
				$elementsToChange['begin'] = true;

				if ($oldEvent->getBegin()->format('d-m-Y') != $event->getBegin()->format('d-m-Y')) {
					$elementsToChange['beginDate'] = true;
				}

				if ($oldEvent->getBegin()->format('H') != $event->getBegin()->format('H')) {
					$elementsToChange['beginHour'] = true;
				}
			}

			if ($oldEvent->getEnd() != $event->getEnd()) {
				$elementsToChange['end'] = true;

				if ($oldEvent->getEnd()->format('d-m-Y') != $event->getEnd()->format('d-m-Y')) {
					$elementsToChange['endDate'] = true;
				}

				if ($oldEvent->getEnd()->format('H') != $event->getEnd()->format('H')) {
					$elementsToChange['endHour'] = true;
				}
			}

			if ($oldEvent->getLocation() != $event->getLocation()) {
				$elementsToChange['location'] = true;
			}

			if (
				$oldEvent->getDescription() != $event->getDescription()
				|| $oldEvent->getTitle() != $event->getTitle()
				|| $oldEvent->getCategory() != $event->getCategory()
			) {
				$elementsToChange['other'] = true;
			}

			$entity = array(
				'event' => array(
					'id' => $event->getId(),
					'title' => $event->getTitle(),
					'location' => $event->getLocation(),
					'begin' => $event->getBegin(),
					'end' => $event->getEnd(),
					'orga' => array(
						'id' => $event->getOrga()->getId(),
						'name' => $event->getOrga()->getName(),
					)
				),
				'changes' => $elementsToChange
			);


			// Send notifications to subscribers
			$notif = new Notification();

			$notif
				->setModule($this->getCurrentBundle()->getIdentifier())
				->setHelper('event_edited')
				->setAuthorId($this->getUser()->getId())
				->setEntityType('event')
				->setEntityId($event->getId())
				->addEntity($entity);

			$this->getNotificationsSender()->send($notif);

			// Confirmation
			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'events.memberships.edit.confirm'
			));

			return $this->redirect($this->generateUrl('memberships_orga_events_edit', array(
				'login' => $login,
				'id' => $id,
				'slug' => $slug
			)));
		}

		return array(
			'memberships' => $memberships,
			'membership' => $membership,
			'orga' => $orga,
			'event' => $event,
			'form' => $form->createView(),
		);
	}

	/**
	 * @Route(
	 *      "/user/membership/{login}/event/{id}-{slug}/delete/{confirm}",
	 *      defaults={"confirm"=false},
	 *      name="memberships_orga_events_delete"
	 * )
	 * @Template()
	 */
	public function deleteAction(Request $request, $login, $id, $slug, $confirm = false)
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $memberships Member[] */
		$memberships = $em->createQueryBuilder()
			->select('m, o')
			->from('EtuUserBundle:Member', 'm')
			->leftJoin('m.organization', 'o')
			->andWhere('m.user = :user')
			->setParameter('user', $this->getUser()->getId())
			->orderBy('m.role', 'DESC')
			->addOrderBy('o.name', 'ASC')
			->getQuery()
			->getResult();

		$membership = null;

		foreach ($memberships as $m) {
			if ($m->getOrganization()->getLogin() == $login) {
				$membership = $m;
				break;
			}
		}

		if (! $membership) {
			throw $this->createNotFoundException('Membership or organization not found for login '.$login);
		}

		if (! $membership->hasPermission('events')) {
			return $this->createAccessDeniedResponse();
		}

		$orga = $membership->getOrganization();

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

		if ($event->getOrga()->getId() != $orga->getId()) {
			return $this->createAccessDeniedResponse();
		}

		if ($confirm) {

			$entity = array(
				'id' => $event->getId(),
				'title' => $event->getTitle(),
				'location' => $event->getLocation(),
				'begin' => $event->getBegin(),
				'end' => $event->getEnd(),
				'orga' => array(
					'id' => $event->getOrga()->getId(),
					'name' => $event->getOrga()->getName(),
				)
			);

			// Send notifications to subscribers
			$notif = new Notification();

			$notif
				->setModule($this->getCurrentBundle()->getIdentifier())
				->setHelper('event_deleted')
				->setAuthorId($this->getUser()->getId())
				->setEntityType('event')
				->setEntityId($event->getId())
				->addEntity($entity);

			$this->getNotificationsSender()->send($notif);

			$em->createQueryBuilder()
				->delete()
				->from('EtuModuleEventsBundle:Answer', 'a')
				->where('a.event = :id')
				->setParameter('id', $event->getId())
				->getQuery()
				->execute();

			$em->remove($event);
			$em->flush();

			// Confirmation
			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'events.memberships.delete.confirm'
			));

			return $this->redirect($this->generateUrl('memberships_orga_events', array(
				'login' => $login
			)));
		}

		return array(
			'memberships' => $memberships,
			'membership' => $membership,
			'orga' => $orga,
			'event' => $event
		);
	}
}
