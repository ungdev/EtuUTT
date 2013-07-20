<?php

namespace Etu\Module\EventsBundle\Controller;

use CalendR\Period\Month;
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

class MembershipsController extends Controller
{
	/**
	 * @Route(
	 *      "/user/membership/{login}/events/create",
	 *      name="memberships_orga_events_create"
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
			->add('file', 'file')
			->add('location', 'textarea')
			->add('description', 'redactor')
			->getForm();

		if ($request->getMethod() == 'POST' && $form->submit($request)->isValid()) {
			$event->setDescription(RedactorJsEscaper::escape($event->getDescription()));

			$em->persist($event);
			$em->flush();

			$event->upload();

			$entity = array(
				'id' => $event->getId(),
				'title' => $event->getTitle(),
				'category' => $categories[$event->getCategory()],
				'orga' => array(
					'name' => $event->getOrga()->getName()
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
				'login' => $login,
				'month' => $event->getBegin()->format('m-Y')
			)));
		}

		return array(
			'memberships' => $memberships,
			'membership' => $membership,
			'orga' => $orga,
			'form' => $form->createView(),
		);
	}

	/**
	 * @Route(
	 *      "/user/membership/{login}/events/{month}",
	 *      defaults={"day" = "current"},
	 *      name="memberships_orga_events"
	 * )
	 * @Template()
	 */
	public function eventsAction($login, $month = 'current')
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
			'memberships' => $memberships,
			'membership' => $membership,
			'orga' => $orga,
			'month' => $month,
			'monthsList' => $monthsList,
			'previous' => $previous,
			'next' => $next,
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
			->add('begin', null, array(
				'attr' => array('class' => 'event-select-date'),
				'widget' => 'choice',
				'minutes' => array(0)
			))
			->add('end', null, array(
				'attr' => array('class' => 'event-select-date'),
				'widget' => 'choice',
				'minutes' => array(0)
			))
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
