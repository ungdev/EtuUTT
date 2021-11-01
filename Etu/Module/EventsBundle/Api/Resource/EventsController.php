<?php

namespace Etu\Module\EventsBundle\Api\Resource;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Framework\Annotation\Scope;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;
use Etu\Module\EventsBundle\Entity\Event;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class EventsController extends ApiController
{
    /**
     * This endpoint gives you a list of public events.
     *
     * @ApiDoc(
     *   section = "Events",
     *   description = "List of all public events (privacy <= 200). Do not require requests to have oauth credentials (scope: external)",
     *   parameters = {
     *      {
     *          "name" = "category",
     *          "required" = false,
     *          "dataType" = "string",
     *          "format" = "culture/sport/soiree/formation/autre",
     *          "description" = "Filter by the chosen category"
     *      },
     *      {
     *          "name" = "after",
     *          "required" = false,
     *          "dataType" = "number",
     *          "format" = "YYYY-MM-DD HH:mm:ss",
     *          "description" = "Filter by begin date after the given date"
     *      },
     *      {
     *          "name" = "before",
     *          "required" = false,
     *          "dataType" = "number",
     *          "format" = "YYYY-MM-DD HH:mm:ss",
     *          "description" = "Filter by begin date before the given date"
     *      },
     *      {
     *          "name" = "organization",
     *          "required" = false,
     *          "dataType" = "number",
     *          "description" = "Filter by organization login"
     *      },
     *      {
     *          "name" = "fields",
     *          "required" = false,
     *          "dataType" = "string",
     *          "format" = "title category begin end isAllDay location description privacy orga",
     *          "description" = "Get required fields only, increase performances"
     *      }
     *   }
     * )
     *
     * @Route("/public/events", name="api_public_events_list", options={"expose"=true})
     * @Method("GET")
     * @Scope("external")
     */
    public function publicListAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var $query QueryBuilder */
        $query = $em->createQueryBuilder()
          ->select('e')
          ->from('EtuModuleEventsBundle:Event', 'e')
          ->leftJoin('e.orga', 'o')
          ->where('e.deletedAt IS NULL')
          ->andWhere('e.privacy <= :privacy')
          ->setParameter('privacy', Event::PRIVACY_PRIVATE);

        if ($request->query->has('category')) {
            $category = $request->query->get('category');
            $query->andWhere('e.category = :category')
            ->setParameter('category', $category);
        }
        if ($request->query->has('after')) {
            $after = $request->query->get('after');
            $query->andWhere('e.begin > :after')
            ->setParameter('after', $after);
        }
        if ($request->query->has('before')) {
            $before = $request->query->get('before');
            $query->andWhere('e.begin < :before')
            ->setParameter('before', $before);
        }
        if ($request->query->has('organization')) {
            $organization = $request->query->get('organization');
            $query->andWhere('o.login = :orga')
            ->setParameter('orga', $organization);
        }
        $fields = ['title', 'category', 'begin', 'end', 'isAllDay', 'privacy', 'orga'];
        if ($request->query->has('fields')) {
            $fields = explode(' ', $request->query->get('fields'));
        }
        $events = $query->getQuery()->getResult();

        return $this->format([
      'events' => $this->get('etu.api.event.transformer')->transform($events, new EmbedBag($fields)),
    ], 200, [], $request);
    }

    /**
     * This endpoint gives you a list of events.
     *
     * @ApiDoc(
     *   section = "Events",
     *   description = "List of all events (scope: public)",
     *   parameters = {
     *      {
     *          "name" = "category",
     *          "required" = false,
     *          "dataType" = "string",
     *          "format" = "culture/sport/soiree/formation/autre",
     *          "description" = "Filter by the chosen category"
     *      },
     *      {
     *          "name" = "after",
     *          "required" = false,
     *          "dataType" = "number",
     *          "format" = "YYYY-MM-DD HH:mm:ss",
     *          "description" = "Filter by begin date after the given date"
     *      },
     *      {
     *          "name" = "before",
     *          "required" = false,
     *          "dataType" = "number",
     *          "format" = "YYYY-MM-DD HH:mm:ss",
     *          "description" = "Filter by begin date before the given date"
     *      },
     *      {
     *          "name" = "organization",
     *          "required" = false,
     *          "dataType" = "number",
     *          "description" = "Filter by organization ID"
     *      },
     *      {
     *          "name" = "privacy",
     *          "required" = false,
     *          "dataType" = "number",
     *          "description" = "Filter by event privacy : public events = 100, private events = 200 (student only), orga events = 300 (member of at least 1 organization), member event = 400 (member of the organization)"
     *      },
     *      {
     *          "name" = "fields",
     *          "required" = false,
     *          "dataType" = "string",
     *          "format" = "title category begin end isAllDay location description privacy orga",
     *          "description" = "Get required fields only, increase performances"
     *      }
     *   }
     * )
     *
     * @Route("/events", name="api_events_list", options={"expose"=true})
     * @Method("GET")
     * @Scope("public")
     */
    public function listAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var $query QueryBuilder */
        $query = $em->createQueryBuilder()
         ->select('e')
         ->from('EtuModuleEventsBundle:Event', 'e')
         ->leftJoin('e.orga', 'o')
         ->where('e.deletedAt IS NULL');

        if ($request->query->has('category')) {
            $category = $request->query->get('category');
            $query->andWhere('e.category = :category')
           ->setParameter('category', $category);
        }
        if ($request->query->has('privacy')) {
            $privacy = $request->query->get('privacy');
            $query->andWhere('e.privacy = :privacy')
          ->setParameter('privacy', $privacy);
        }
        if ($request->query->has('after')) {
            $after = $request->query->get('after');
            $query->andWhere('e.begin > :after')
           ->setParameter('after', $after);
        }
        if ($request->query->has('before')) {
            $before = $request->query->get('before');
            $query->andWhere('e.begin < :before')
           ->setParameter('before', $before);
        }
        if ($request->query->has('organization')) {
            $organization = $request->query->get('organization');
            $query->andWhere('o.login = :orga')
           ->setParameter('orga', $organization);
        }
        $fields = ['id', 'title', 'category', 'begin', 'end', 'isAllDay', 'privacy', 'orga'];
        if ($request->query->has('fields')) {
            $fields = explode(' ', $request->query->get('fields'));
        }
        $events = $query->getQuery()->getResult();

        $thisuser = $this->getAccessToken($request)->getUser();
        /** @var Event $event */
        $events = array_filter($events, function ($event) use ($thisuser) {
            if ($event->getPrivacy() > 300) {
                return $event->getOrga()->hasMembership($thisuser);
            } elseif ($event->getPrivacy() > 200) {
                return count($thisuser->getMemberships()) > 0;
            }

            return true;
        });

        return $this->format([
      'events' => $this->get('etu.api.event.transformer')->transform($events, new EmbedBag($fields)),
    ], 200, [], $request);
    }

    /**
     * This endpoint gives you details of an event.
     *
     * @ApiDoc(
     *   section = "Events",
     *   description = "Get event details (scope: public)"
     * )
     *
     * @Route("/events/{id}", name="api_event_details", options={"expose"=true})
     * @Method("GET")
     *
     * @param mixed $id
     */
    public function viewAction($id, Request $request)
    {
        /** @var Event $event */
        $event = $this->getDoctrine()
        ->getRepository(Event::class)
        ->find($id);

        if (null == $event) {
            return $this->format([
              'error' => 'Unknown event',
          ], 404, [], $request);
        }
        if ($event->getPrivacy() > Event::PRIVACY_MEMBERS) {
            if (!$event->getOrga()->hasMembership($this->getAccessToken($request)->getUser())) {
                return $this->format([
                'error' => 'Unknown event',
            ], 404, [], $request);
            }
        } elseif ($event->getPrivacy() > Event::PRIVACY_ORGAS) {
            if (!count($this->getAccessToken($request)->getUser()->getMemberships()) > 0) {
                return $this->format([
                  'error' => 'Unknown event',
              ], 404, [], $request);
            }
        }

        return $this->format([
          'event' => [
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'orga' => $event->getOrga()->getLogin(),
            'begin' => $event->getBegin(),
            'end' => $event->getEnd(),
            'category' => $event->getCategory(),
            'description' => $event->getDescription(),
            'isAllDay' => $event->getIsAllDay(),
            'location' => $event->getLocation(),
            'privacy' => $event->getPrivacy(),
          ],
        ], 200, [], $request);
    }
}
