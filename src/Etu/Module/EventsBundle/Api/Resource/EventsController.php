<?php

namespace Etu\Module\EventsBundle\Api\Resource;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Framework\Annotation\Scope;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Etu\Module\EventsBundle\Entity\Event;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;

class EventsController extends ApiController
{
  /**
   * This endpoint gives you a list of public events.
   *
   * @ApiDoc(
   *   section = "Events",
   *   description = "List of all events (scope: external)",
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

    $events = $em->getRepository('EtuModuleEventsBundle:Event')->findBy(
      [
        'deletedAt' => null
      ]
    );

    /** @var Event $event */
    $events = array_filter($events, function ($event) {
      if ($event->getPrivacy() > 200) {
        return false;
      }
      return true;
    });

    if ($request->query->has('category')) {
      $category = $request->query->get('category');
      $tmp = [];
      foreach ($events as $event) {
        if ($event->getCategory() == $category)
          $tmp = array_merge($tmp, [$event]);
      }
      $events = $tmp;
    }
    if ($request->query->has('after')) {
      $after = $request->query->get('after');
      $tmp = [];
      foreach ($events as $event) {
        if ($event->getBegin() > $after)
          $tmp = array_merge($tmp, [$event]);
      }
      $events = $tmp;
    }
    if ($request->query->has('before')) {
      $before = $request->query->get('before');
      $tmp = [];
      foreach ($events as $event) {
        if ($event->getBegin() < $before)
          $tmp = array_merge($tmp, [$event]);
      }
      $events = $tmp;
    }
    if ($request->query->has('organization')) {
      $organization = $request->query->get('organization');
      $tmp = [];
      foreach ($events as $event) {
        try {
          $orga = $event->getOrga()->getLogin();
          if ($orga == $organization)
            $tmp = array_merge($tmp, [$event]);
        } catch (\Exception $e) { }
      }
      $events = $tmp;
    }
    $fields = ['title', 'category', 'begin', 'end', 'isAllDay', 'privacy', 'orga'];
    if ($request->query->has('fields')) {
      $fields = explode(' ', $request->query->get('fields'));
    }
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

    $events = $em->getRepository('EtuModuleEventsBundle:Event')->findBy(
      [
        'deletedAt' => null
      ]
    );

    /** @var Event $event */
    $events = array_filter($events, function ($event) {
      if ($event->getPrivacy() > 300) {
        return $event->getOrga()->hasMembership($this->getUser());
      } else if ($event->getPrivacy() > 200) {
        return sizeof($this->getUser()->getMemberships()) > 0;
      }
      return true;
    });

    if ($request->query->has('category')) {
      $category = $request->query->get('category');
      $tmp = [];
      foreach ($events as $event) {
        if ($event->getCategory() == $category)
          $tmp = array_merge($tmp, [$event]);
      }
      $events = $tmp;
    }
    if ($request->query->has('after')) {
      $after = $request->query->get('after');
      $tmp = [];
      foreach ($events as $event) {
        if ($event->getBegin() > $after)
          $tmp = array_merge($tmp, [$event]);
      }
      $events = $tmp;
    }
    if ($request->query->has('before')) {
      $before = $request->query->get('before');
      $tmp = [];
      foreach ($events as $event) {
        if ($event->getBegin() < $before)
          $tmp = array_merge($tmp, [$event]);
      }
      $events = $tmp;
    }
    if ($request->query->has('organization')) {
      $organization = $request->query->get('organization');
      $tmp = [];
      foreach ($events as $event) {
        try {
          $orga = $event->getOrga()->getLogin();
          if ($orga == $organization)
            $tmp = array_merge($tmp, [$event]);
        } catch (\Exception $e) { }
      }
      $events = $tmp;
    }
    $fields = ['title', 'category', 'begin', 'end', 'isAllDay', 'privacy', 'orga'];
    if ($request->query->has('fields')) {
      $fields = explode(' ', $request->query->get('fields'));
    }
    return $this->format([
      'events' => $this->get('etu.api.event.transformer')->transform($events, new EmbedBag($fields)),
    ], 200, [], $request);
  }
}
