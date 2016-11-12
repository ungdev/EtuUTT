<?php

namespace Etu\Module\EventsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use CalendR\Calendar;
use CalendR\Period\Range;
use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Core\CoreBundle\Form\RedactorType;
use Etu\Core\CoreBundle\Form\DatetimePickerType;
use Etu\Core\UserBundle\Entity\Member;
use Etu\Module\EventsBundle\Entity\Event;
// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MembershipsController extends Controller
{
    /**
     * @Route(
     *      "/user/membership/{login}/events/{year}/{month}/{day}",
     *      defaults={"month" = "current", "year" = "current", "day" = "current"},
     *      requirements={"month" = "\d+", "year" = "\d+", "day" = "\d+"},
     *      name="memberships_orga_events"
     * )
     * @Template()
     */
    public function eventsAction($login, $day = 'current', $month = 'current', $year = 'current')
    {
        $this->denyAccessUnlessGranted('ROLE_EVENTS_EDIT');

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

        if (!$membership) {
            throw $this->createNotFoundException('Membership or organization not found for login '.$login);
        }

        if (!$membership->hasPermission('events')) {
            return $this->createAccessDeniedResponse();
        }

        $orga = $membership->getOrganization();

        $event = new Event(null, new \DateTime(), new \DateTime());
        $event->setOrga($orga);

        $categories = [];

        foreach (Event::$categories as $category) {
            $categories[$category] = 'events.categories.'.$category;
        }

        $day = ($day == 'current') ? (int) date('d') : (int) $day;
        $month = ($month == 'current') ? (int) date('m') : (int) $month;
        $year = ($year == 'current') ? (int) date('Y') : (int) $year;

        return [
            'memberships' => $memberships,
            'membership' => $membership,
            'orga' => $orga,
            'day' => $day,
            'month' => $month - 1,
            'year' => $year,
        ];
    }

    /**
     * @Route(
     *      "/user/membership/{login}/events/find",
     *      defaults={"_format"="json"},
     *      name="memberships_orga_events_find",
     *      options={"expose"=true}
     * )
     */
    public function ajaxEventsAction(Request $request, $login)
    {
        $this->denyAccessUnlessGranted('ROLE_EVENTS_EDIT');

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

        if (!$membership) {
            throw $this->createNotFoundException('Membership or organization not found for login '.$login);
        }

        if (!$membership->hasPermission('events')) {
            return $this->createAccessDeniedResponse();
        }

        $orga = $membership->getOrganization();

        $start = $request->query->get('start');
        $end = $request->query->get('end');

        if (!$start) {
            return new Response(json_encode([
                'status' => 'error',
                'message' => '"start" parameter is required',
            ]));
        }

        if (!$end) {
            return new Response(json_encode([
                'status' => 'error',
                'message' => '"end" parameter is required',
            ]));
        }
        $start = \DateTime::createFromFormat('Y-m-d', $start);
        $end = \DateTime::createFromFormat('Y-m-d', $end);

        /** @var Calendar $calendr */
        $calendr = $this->get('calendr');

        /** @var \CalendR\Event\Collection\Basic $events */
        $events = $calendr->getEvents(new Range($start, $end), ['connected' => true]);

        /** @var array $json */
        $json = [];

        /** @var Event $event */
        foreach ($events->all() as $event) {
            if ($event->getOrga()->getId() != $orga->getId()) {
                continue;
            }

            $json[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'start' => $event->getBegin()->format('Y-m-d H:i:00'),
                'end' => $event->getEnd()->format('Y-m-d H:i:00'),
                'allDay' => $event->getIsAllDay(),
                'url' => $this->generateUrl('memberships_orga_events_edit', [
                    'login' => $orga->getLogin(),
                    'id' => $event->getId(),
                    'slug' => StringManipulationExtension::slugify($event->getTitle()),
                ]),
            ];
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
        $this->denyAccessUnlessGranted('ROLE_EVENTS_EDIT');

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

        if (!$membership) {
            throw $this->createNotFoundException('Membership or organization not found for login '.$login);
        }

        if (!$membership->hasPermission('events')) {
            return $this->createAccessDeniedResponse();
        }

        $start = $request->query->get('s');
        $end = $request->query->get('e');
        $allDay = ($request->query->get('a') == 'true') ? true : false;

        if (!$start || !$end) {
            throw $this->createNotFoundException();
        }

        $orga = $membership->getOrganization();

        if (substr($start, 12) == '00-00' && substr($end, 12) == '00-00') {
            $start = substr($start, 0, 12).'12-00';

            $jour2 = str_pad((substr($end, 0, 2) - 1), 2, '0', STR_PAD_LEFT);
            $end = $jour2.substr($end, 2, 10).'13-00';
        }

        $event = new Event(
            null,
            \DateTime::createFromFormat('d-m-Y--H-i', $start),
            \DateTime::createFromFormat('d-m-Y--H-i', $end)
        );

        $event->setOrga($orga)
            ->setIsAllDay($allDay);

        $categories = [];

        foreach (Event::$categories as $category) {
            $categories['events.categories.'.$category] = $category;
        }

        $form = $this->createFormBuilder($event)
            ->add('title', TextType::class, ['label' => 'events.memberships.edit.title'])
            ->add('begin', DatetimePickerType::class, ['label' => 'events.memberships.edit.begin'])
            ->add('end', DatetimePickerType::class, ['label' => 'events.memberships.edit.end'])
            ->add('category', ChoiceType::class, ['choices' => $categories, 'label' => 'events.memberships.edit.category'])
            ->add('file', FileType::class, ['required' => false, 'label' => 'events.memberships.create.image.label', 'attr' => ['help' => 'events.memberships.create.image.explain']])
            ->add('location', TextareaType::class, ['label' => 'events.memberships.edit.location'])
            ->add('privacy', ChoiceType::class, [
                'choices' => [
                    'events.memberships.create.privacy.public' => Event::PRIVACY_PUBLIC,
                    'events.memberships.create.privacy.private' => Event::PRIVACY_PRIVATE,
                    'events.memberships.create.privacy.orgas' => Event::PRIVACY_ORGAS,
                    'events.memberships.create.privacy.members' => Event::PRIVACY_MEMBERS,
                ],
                'required' => true,
                'label' => 'events.memberships.create.privacy.label',
            ])
            ->add('description', RedactorType::class, ['label' => 'events.memberships.edit.description'])
            ->add('submit', SubmitType::class, ['label' => 'events.memberships.create.submit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($event);
            $em->flush();

            $event->upload();

            if (!in_array($event->getPrivacy(), [Event::PRIVACY_ORGAS, Event::PRIVACY_MEMBERS])) {
                $entity = [ // @TODO WTF? Y U AN ARRAY?
                    'id' => $event->getId(),
                    'title' => $event->getTitle(),
                    'category' => array_flip($categories)[$event->getCategory()],
                    'orga' => [
                        'id' => $event->getOrga()->getId(),
                        'name' => $event->getOrga()->getName(),
                    ],
                ];

                // Send notifications to subscribers of all eventts
                $notif = new Notification();

                $notif
                    ->setModule('events')
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
                    ->setModule('events')
                    ->setHelper('event_created_category')
                    ->setAuthorId($this->getUser()->getId())
                    ->setEntityType('event-category')
                    ->setEntityId($keys[$event->getCategory()])
                    ->addEntity($entity);

                $this->getNotificationsSender()->send($notif);
            }

            // Confirmation
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'events.memberships.create.confirm',
            ]);

            return $this->redirect($this->generateUrl('memberships_orga_events', [
                'login' => $login,
                'day' => $event->getBegin()->format('d'),
                'month' => $event->getBegin()->format('m'),
                'year' => $event->getBegin()->format('Y'),
            ]));
        }

        return [
            'memberships' => $memberships,
            'membership' => $membership,
            'orga' => $orga,
            'form' => $form->createView(),
            'event' => $event,
        ];
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
        $this->denyAccessUnlessGranted('ROLE_EVENTS_EDIT');

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

        if (!$membership) {
            throw $this->createNotFoundException('Membership or organization not found for login '.$login);
        }

        if (!$membership->hasPermission('events')) {
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

        if (!$event) {
            throw $this->createNotFoundException('Event #'.$id.' not found');
        }

        if (StringManipulationExtension::slugify($event->getTitle()) != $slug) {
            return $this->redirect($this->generateUrl('events_view', [
                'id' => $id, 'slug' => StringManipulationExtension::slugify($event->getTitle()),
            ]), 301);
        }

        if ($event->getOrga()->getId() != $orga->getId()) {
            return $this->createAccessDeniedResponse();
        }

        $categories = [];

        foreach (Event::$categories as $category) {
            $categories['events.categories.'.$category] = $category;
        }

        $form = $this->createFormBuilder($event)
            ->add('title', TextType::class, ['label' => 'events.memberships.edit.title'])
            ->add('begin', DatetimePickerType::class, ['label' => 'events.memberships.edit.begin'])
            ->add('end', DatetimePickerType::class, ['label' => 'events.memberships.edit.end'])
            ->add('category', ChoiceType::class, ['choices' => $categories, 'label' => 'events.memberships.edit.category'])
            ->add('file', FileType::class, ['required' => false, 'label' => 'events.memberships.create.image.label', 'attr' => ['help' => 'events.memberships.create.image.explain']])
            ->add('location', TextareaType::class, ['label' => 'events.memberships.edit.location'])
            ->add('privacy', ChoiceType::class, [
                'choices' => [
                    'events.memberships.create.privacy.public' => Event::PRIVACY_PUBLIC,
                    'events.memberships.create.privacy.private' => Event::PRIVACY_PRIVATE,
                    'events.memberships.create.privacy.orgas' => Event::PRIVACY_ORGAS,
                    'events.memberships.create.privacy.members' => Event::PRIVACY_MEMBERS,
                ],
                'required' => true,
                'label' => 'events.memberships.create.privacy.label',
            ])
            ->add('description', RedactorType::class, ['label' => 'events.memberships.edit.description'])
            ->add('submit', SubmitType::class, ['label' => 'events.memberships.edit.submit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($event);
            $em->flush();

            $event->upload();

            // Confirmation
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'events.memberships.edit.confirm',
            ]);

            return $this->redirect($this->generateUrl('memberships_orga_events_edit', [
                'login' => $login,
                'id' => $id,
                'slug' => $slug,
            ]));
        }

        return [
            'memberships' => $memberships,
            'membership' => $membership,
            'orga' => $orga,
            'event' => $event,
            'form' => $form->createView(),
            'rand' => substr(md5(uniqid(true)), 0, 5),
        ];
    }

    /**
     * @Route(
     *      "/user/membership/{login}/events/edit/{id}",
     *      defaults={"_format"="json"},
     *      name="memberships_orga_events_ajax_edit",
     *      options={"expose"=true}
     * )
     * @Template()
     */
    public function ajaxEditAction(Request $request, $login, Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_EVENTS_EDIT');

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

        if (!$membership) {
            throw $this->createNotFoundException('Membership or organization not found for login '.$login);
        }

        if (!$membership->hasPermission('events')) {
            return $this->createAccessDeniedResponse();
        }

        $orga = $membership->getOrganization();

        if ($event->getOrga()->getId() != $orga->getId()) {
            return $this->createAccessDeniedResponse();
        }

        $eventUpdate = $request->request->get('event');

        if (!$eventUpdate) {
            throw $this->createNotFoundException('No event patch provided');
        }

        $oldInterval = $event->getEnd()->diff($event->getBegin());

        if (isset($eventUpdate['allDay'])) {
            $event->setIsAllDay($eventUpdate['allDay'] == 'true');
            $oldInterval = \DateInterval::createFromDateString('1 second');
        }

        if (isset($eventUpdate['start'])) {
            $event->setBegin(\DateTime::createFromFormat('d-m-Y--H-i', $eventUpdate['start']));

            $end = \DateTime::createFromFormat('d-m-Y--H-i', $eventUpdate['start']);
            $end->add($oldInterval);

            $event->setEnd($end);
        }

        if (isset($eventUpdate['end'])) {
            $event->setEnd(\DateTime::createFromFormat('d-m-Y--H-i', $eventUpdate['end']));
        }

        $em->persist($event);
        $em->flush();

        return new Response(json_encode([
            'status' => 'success',
        ]));
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
        $this->denyAccessUnlessGranted('ROLE_EVENTS_EDIT');

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

        if (!$membership) {
            throw $this->createNotFoundException('Membership or organization not found for login '.$login);
        }

        if (!$membership->hasPermission('events')) {
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

        if (!$event) {
            throw $this->createNotFoundException('Event #'.$id.' not found');
        }

        if (StringManipulationExtension::slugify($event->getTitle()) != $slug) {
            return $this->redirect($this->generateUrl('events_view', [
                'id' => $id, 'slug' => StringManipulationExtension::slugify($event->getTitle()),
            ]), 301);
        }

        if ($event->getOrga()->getId() != $orga->getId()) {
            return $this->createAccessDeniedResponse();
        }

        if ($confirm) {
            $entity = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'location' => $event->getLocation(),
                'begin' => $event->getBegin(),
                'end' => $event->getEnd(),
                'orga' => [
                    'id' => $event->getOrga()->getId(),
                    'name' => $event->getOrga()->getName(),
                ],
            ];

            // Send notifications to subscribers
            $notif = new Notification();

            $notif
                ->setModule('events')
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
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'events.memberships.delete.confirm',
            ]);

            return $this->redirect($this->generateUrl('memberships_orga_events', [
                'login' => $login,
            ]));
        }

        return [
            'memberships' => $memberships,
            'membership' => $membership,
            'orga' => $orga,
            'event' => $event,
        ];
    }
}
