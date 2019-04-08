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
     *      }
     *   }
     * )
     *
     * @Route("/events", name="api_events_list", options={"expose"=true})
     * @Method("GET")
     * @Scope("external")
     */
    public function listAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var $query QueryBuilder */
        $query = $em->createQueryBuilder()
            ->select('e.title, o.login as organization, e.category, e.begin, e.end, e.location, e.description, e.privacy')
            ->from('EtuModuleEventsBundle:Event', 'e')
            ->leftJoin('e.orga', 'o')
            ->where('e.deletedAt IS NULL')
            ->andWhere('e.privacy <= 200');
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
            $query->andWhere('o.login = :organization')
              ->setParameter('organization', $organization);
        }
        /** @var Event[] $events */
        $events = $query->getQuery()->getResult();

        return $this->format(['events' => $events], 200, [], $request);
    }
}
