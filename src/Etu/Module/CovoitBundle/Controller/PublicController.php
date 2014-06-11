<?php

namespace Etu\Module\CovoitBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\CovoitBundle\Entity\Covoit;
use Etu\Module\CovoitBundle\Entity\CovoitMessage;

// Import annotations
use Etu\Module\CovoitBundle\Model\Search;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/covoiturage")
 * @Template()
 */
class PublicController extends Controller
{
    /**
     * @Route("/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="covoiturage_index")
     * @Template()
     */
    public function indexAction($page = 1)
    {
        if (! $this->getUserLayer()->isUser()) {
            return $this->createAccessDeniedResponse();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('c, cs, ca')
            ->from('EtuModuleCovoitBundle:Covoit', 'c')
            ->leftJoin('c.author', 'ca')
            ->leftJoin('c.subscriptions', 'cs')
            ->where('c.date > CURRENT_DATE()')
            ->orderBy('c.date', 'DESC')
            ->getQuery();

        /** @var Covoit[] $covoits */
        $covoits = $this->get('knp_paginator')->paginate($query, $page, 20);

        $search = new Search();
        $search->startCity = $em->getRepository('EtuCoreBundle:City')->find(749);

        $searchForm = $this->createForm($this->get('etu.covoit.form.search'), $search);

        return [
            'pagination' => $covoits,
            'searchForm' => $searchForm->createView(),
            'today' => new \DateTime()
        ];
    }

    /**
     * @Route("/search/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="covoiturage_search")
     * @Template()
     */
    public function searchAction(Request $request, $page = 1)
    {
        if (! $this->getUserLayer()->isUser()) {
            return $this->createAccessDeniedResponse();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $search = new Search();
        $search->startCity = $em->getRepository('EtuCoreBundle:City')->find(749);

        $searchForm = $this->createForm($this->get('etu.covoit.form.search'), $search);
        $pagination = false;

        if ($searchForm->submit($request)->isValid()) {
            // Search covoits
            $query = $this->get('etu.covoit.query_mapper.search')->map($em->createQueryBuilder(), $search)->getQuery();

            $pagination = $this->get('knp_paginator')->paginate($query, $page, 20);
        }

        return [
            'pagination' => $pagination,
            'searchForm' => $searchForm->createView(),
            'today' => new \DateTime()
        ];
    }

    /**
     * @Route("/{slug}-{id}", requirements={"slug"=".+"}, name="covoiturage_view")
     * @Template()
     */
    public function viewAction(Request $request, $id, $slug)
    {
        if (! $this->getUserLayer()->isUser()) {
            return $this->createAccessDeniedResponse();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Covoit $covoit */
        $covoit = $em->createQueryBuilder()
            ->select('c, cs, ca')
            ->from('EtuModuleCovoitBundle:Covoit', 'c')
            ->leftJoin('c.author', 'ca')
            ->leftJoin('c.subscriptions', 'cs')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (! $covoit) {
            throw $this->createNotFoundException('Covoit not found');
        }

        // One URL to rule them all
        if ($slug != $covoit->getStartCity()->getSlug() . '-' . $covoit->getEndCity()->getSlug()) {
            return $this->redirect($this->generateUrl('covoiturage_view', [
                'id' => $covoit->getId(),
                'slug' => $covoit->getStartCity()->getSlug() . '-' . $covoit->getEndCity()->getSlug()
            ]), 301);
        }

        $message = new CovoitMessage();
        $message->setAuthor($this->getUser());
        $message->setCovoit($covoit);

        $messageForm = $this->createForm($this->get('etu.covoit.form.message'), $message);

        if ($request->getMethod() == 'POST' && $messageForm->submit($request)->isValid()) {
            $em->persist($message);
            $em->flush();

            // Send notifications to subscribers
            $notif = new Notification();

            $notif
                ->setModule($this->getCurrentBundle()->getIdentifier())
                ->setHelper('covoit_new_message')
                ->setAuthorId($this->getUser()->getId())
                ->setEntityType('covoit')
                ->setEntityId($covoit->getId())
                ->addEntity($message);

            $this->getNotificationsSender()->send($notif);

            // Add current user as subscriber
            $this->getSubscriptionsManager()->subscribe($this->getUser(), 'covoit', $covoit->getId());

            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'success',
                'message' => 'covoit.messages.message_sent'
            ));

            return $this->redirect($this->generateUrl('covoiturage_view', [
                'id' => $covoit->getId(),
                'slug' => $covoit->getStartCity()->getSlug() . '-' . $covoit->getEndCity()->getSlug()
            ]));
        }

        return [
            'covoit' => $covoit,
            'messageForm' => $messageForm->createView()
        ];
    }
}
