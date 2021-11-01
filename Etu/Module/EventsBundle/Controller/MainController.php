<?php

namespace Etu\Module\EventsBundle\Controller;

use CalendR\Calendar;
use CalendR\Period\Range;
use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Core\UserBundle\Entity\User;
use Etu\Module\EventsBundle\Entity\Answer;
use Etu\Module\EventsBundle\Entity\Event;
use Html2Text\Html2Text;
use Sabre\VObject\Component\VCalendar;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
// Import annotations
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MainController extends Controller
{
    /**
     * @Route("/events/{category}", defaults={"category" = "all"}, name="events_index")
     * @Template()
     *
     * @param mixed $category
     */
    public function indexAction($category = 'all')
    {
        $availableCategories = Event::$categories;
        array_unshift($availableCategories, 'all');

        if (!in_array($category, $availableCategories)) {
            throw $this->createNotFoundException(sprintf('Invalid category "%s"', $category));
        }

        $keys = array_flip($availableCategories);

        return [
            'availableCategories' => $availableCategories,
            'currentCategory' => $category,
            'currentCategoryId' => $keys[$category],
        ];
    }

    /**
     * @Route(
     *      "/events/{category}/find",
     *      defaults={"_format" = "json", "category" = "all"},
     *      name="events_find",
     *      options={"expose"=true}
     * )
     *
     * @param mixed $category
     */
    public function ajaxEventsAction(Request $request, $category = 'all')
    {
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
        $events = $calendr->getEvents(new Range($start, $end), [
            'connected' => $this->isGranted('ROLE_EVENTS_INTERNAL'),
        ]);

        /** @var array $json */
        $json = [];

        /** @var Event $event */
        foreach ($events->all() as $event) {
            if ('all' != $category && $event->getCategory() != $category) {
                continue;
            }
            if (Event::PRIVACY_ORGAS == $event->getPrivacy() && $this->getUser()->getMemberships()->count() <= 0) {
                continue;
            }
            if (Event::PRIVACY_MEMBERS == $event->getPrivacy()) {
                $continue = true;
                foreach ($this->getUser()->getMemberships() as $membership) {
                    if ($membership->getOrganization()->getId() == $event->getOrga()->getId()) {
                        $continue = false;
                        break;
                    }
                }
                if ($continue) {
                    continue;
                }
            }

            $json[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'start' => $event->getBegin()->format('Y-m-d H:i:00'),
                'end' => $event->getEnd()->format('Y-m-d H:i:00'),
                'allDay' => $event->getIsAllDay(),
                'url' => $this->generateUrl('events_view', [
                    'id' => $event->getId(),
                    'slug' => StringManipulationExtension::slugify($event->getTitle()),
                ]),
            ];
        }

        return new Response(json_encode($json));
    }

    /**
     * @Route("/event/{id}-{slug}", name="events_view")
     * @Template()
     *
     * @param mixed $id
     * @param mixed $slug
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

        if (!$event) {
            throw $this->createNotFoundException('Event #'.$id.' not found');
        }

        if (Event::PRIVACY_PUBLIC != $event->getPrivacy()) {
            $this->denyAccessUnlessGranted('ROLE_EVENTS_INTERNAL');
        }

        if (Event::PRIVACY_ORGAS == $event->getPrivacy() && $this->getUser()->getMemberships()->count() <= 0) {
            throw new AccessDeniedHttpException();
        }
        if (Event::PRIVACY_MEMBERS == $event->getPrivacy()) {
            $error = true;
            foreach ($this->getUser()->getMemberships() as $membership) {
                if ($membership->getOrganization()->getId() == $event->getOrga()->getId()) {
                    $error = false;
                    break;
                }
            }
            if ($error) {
                throw new AccessDeniedHttpException();
            }
        }

        if (StringManipulationExtension::slugify($event->getTitle()) != $slug) {
            return $this->redirect($this->generateUrl('events_view', [
                'id' => $id, 'slug' => StringManipulationExtension::slugify($event->getTitle()),
            ]), 301);
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

        $answersYes = [];
        $answersProbably = [];
        $userAnswer = false;

        foreach ($answers as $answer) {
            if (Answer::ANSWER_YES == $answer->getAnswer()) {
                $answersYes[] = $answer;
            } elseif (Answer::ANSWER_PROBABLY == $answer->getAnswer()) {
                $answersProbably[] = $answer;
            }

            if ($this->getUser() && $answer->getUser()->getId() == $this->getUser()->getId()) {
                $userAnswer = $answer;
            }
        }

        if ($event->getBegin() == $event->getEnd() && '00:00' == $event->getBegin()->format('H:i')) {
            $useOn = true;
        } else {
            $useOn = false;
        }

        return [
            'event' => $event,
            'useOn' => $useOn,
            'userAnswer' => $userAnswer,
            'answersYesCount' => count($answersYes),
            'answersProbablyCount' => count($answersProbably),
        ];
    }

    /**
     * @Route("/event/{id}-{slug}/members", name="events_members")
     * @Template()
     *
     * @param mixed $id
     * @param mixed $slug
     */
    public function membersAction($id, $slug)
    {
        $this->denyAccessUnlessGranted('ROLE_EVENTS_ANSWER');

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

        if (!$event) {
            throw $this->createNotFoundException('Event #'.$id.' not found');
        }

        if (StringManipulationExtension::slugify($event->getTitle()) != $slug) {
            return $this->redirect($this->generateUrl('events_view', [
                'id' => $id, 'slug' => StringManipulationExtension::slugify($event->getTitle()),
            ]), 301);
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

        $answersYes = [];
        $answersProbably = [];
        $answersNo = [];

        foreach ($answers as $answer) {
            if (Answer::ANSWER_YES == $answer->getAnswer()) {
                $answersYes[] = $answer;
            } elseif (Answer::ANSWER_PROBABLY == $answer->getAnswer()) {
                $answersProbably[] = $answer;
            } else {
                $answersNo[] = $answer;
            }
        }

        return [
            'event' => $event,
            'answersYesCount' => count($answersYes),
            'answersProbablyCount' => count($answersProbably),
            'answersNoCount' => count($answersNo),
            'answersYes' => $answersYes,
            'answersProbably' => $answersProbably,
            'answersNo' => $answersNo,
        ];
    }

    /**
     * @Route("/event/{id}/answer/{answer}", name="events_answer", options={"expose" = true})
     * @Template()
     *
     * @param mixed $id
     * @param mixed $answer
     */
    public function answerAction($id, $answer)
    {
        $this->denyAccessUnlessGranted('ROLE_EVENTS_ANSWER_POST');

        if (!in_array($answer, [Answer::ANSWER_YES, Answer::ANSWER_NO, Answer::ANSWER_PROBABLY])) {
            return new Response(json_encode([
                'status' => 'error',
                'message' => 'Invalid answer',
            ]), 500);
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

        if (!$event) {
            return new Response(json_encode([
                'status' => 'error',
                'message' => 'Event #'.$id.' not found',
            ]), 404);
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

        if (!$userAnswer) {
            $userAnswer = new Answer($event, $this->getUser(), $answer);
        } else {
            $userAnswer->setAnswer($answer);
        }

        $em->persist($userAnswer);
        $em->flush();

        if (Answer::ANSWER_YES == $answer || Answer::ANSWER_PROBABLY == $answer) {
            $this->getSubscriptionsManager()->subscribe($this->getUser(), 'event', $event->getId());
        } else {
            $this->getSubscriptionsManager()->unsubscribe($this->getUser(), 'event', $event->getId());
        }

        return new Response(json_encode([
            'status' => 'success',
            'message' => 'Ok',
        ]), 200);
    }

    /**
     * @Route("/events/tv/", name="events_tv")
     * @Route("/events/tv/index")
     * @Template()
     */
    public function tvAction()
    {
        /** @var Calendar $calendr */
        $calendr = $this->get('calendr');

        $start = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $start->sub(new \DateInterval('PT1H'));
        $end = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $end->add(new \DateInterval('P1M'));

        /** @var \CalendR\Event\Collection\Basic $events */
        $events = $calendr->getEvents(new Range($start, $end), [
            'connected' => true, // TODO add better security with private events
        ]);

        $array = [];
        foreach ($events->all() as $event) {
            if (Event::PRIVACY_ORGAS == $event->getPrivacy() && (!($this->getUser() instanceof User) || $this->getUser()->getMemberships()->count() <= 0)) {
                continue;
            }
            if (Event::PRIVACY_MEMBERS == $event->getPrivacy()) {
                $continue = true;
                if (!($this->getUser() instanceof User)) {
                    foreach ($this->getUser()->getMemberships() as $membership) {
                        if ($membership->getOrganization()->getId() == $event->getOrga()->getId()) {
                            $continue = false;
                            break;
                        }
                    }
                    if ($continue) {
                        continue;
                    }
                }
            }
            $array[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'begin' => $event->getBegin(),
                'location' => $event->getLocation(),
            ];
            if (count($array) >= 4) {
                break;
            }
        }

        return [
            'events' => $array,
        ];
    }

    /**
     * @Route("/events/export", name="user_events_export")
     * @Template()
     */
    public function exportAction()
    {
        return $this->generateVCalendar($this->getUser());
    }

    /**
     * @Route("/events/export/{token}/schedule.ics", name="user_token_events_export")
     * @Template()
     *
     * @param mixed $token
     */
    public function exportTokenAction($token)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->createQueryBuilder()
            ->select('u')
            ->from('EtuUserBundle:User', 'u')
            ->where('u.privateToken = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getSingleResult();

        if (!$user) {
            $this->createAccessDeniedException();
        }

        return $this->generateVCalendar($user);
    }

    protected function generateVCalendar(User $user)
    {
        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $vcalendar = new VCalendar();

        $start = new \DateTime('last week', new \DateTimeZone('Europe/Paris'));
        $end = new \DateTime('next year', new \DateTimeZone('Europe/Paris'));

        /** @var Calendar $calendr */
        $calendr = $this->get('calendr');

        /** @var \CalendR\Event\Collection\Basic $events */
        $events = $calendr->getEvents(new Range($start, $end), [
            'connected' => $this->isGranted('ROLE_EVENTS_INTERNAL'),
        ]);

        /** @var array $json */
        $json = [];

        /** @var Event $event */
        foreach ($events->all() as $event) {
            if (Event::PRIVACY_ORGAS == $event->getPrivacy() && $user->getMemberships()->count() <= 0) {
                continue;
            }
            if (Event::PRIVACY_MEMBERS == $event->getPrivacy()) {
                $continue = true;
                foreach ($user->getMemberships() as $membership) {
                    if ($membership->getOrganization()->getId() == $event->getOrga()->getId()) {
                        $continue = false;
                        break;
                    }
                }
                if ($continue) {
                    continue;
                }
            }
            $description = (new Html2Text($event->getDescription()))->getText();
            $url = $this->generateUrl('events_view', [
                'id' => $event->getId(),
                'slug' => StringManipulationExtension::slugify($event->getTitle()),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $description .= "\n $url";
            $vcalendar->add('VEVENT', [
                'SUMMARY' => $event->getOrga()->getFullName().' : '.$event->getTitle(),
                'DTSTART' => $event->getBegin(),
                'DTEND' => $event->getEnd(),
                'DESCRIPTION' => $description,
                'LOCATION' => $event->getLocation(),
                'CATEGORIES' => $event->getCategory(),
                'URL' => $url,
            ]);
        }

        $response = new Response($vcalendar->serialize());

        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="etuutt_events.ics"');

        return $response;
    }
}
