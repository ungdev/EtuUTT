<?php

namespace Etu\Module\CovoitBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\CovoitBundle\Entity\Covoit;
use Etu\Module\CovoitBundle\Entity\CovoitMessage;
// Import annotations
use Etu\Module\CovoitBundle\Form\CovoitMessageType;
use Etu\Module\CovoitBundle\Form\SearchType;
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
     *
     * @param mixed $page
     */
    public function indexAction($page = 1)
    {
        $this->denyAccessUnlessGranted('ROLE_COVOIT');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('c, cs, ca')
            ->from('EtuModuleCovoitBundle:Covoit', 'c')
            ->leftJoin('c.author', 'ca')
            ->leftJoin('c.subscriptions', 'cs')
            ->where('c.date >= CURRENT_DATE()')
            ->orderBy('c.date', 'DESC')
            ->getQuery();

        /** @var Covoit[] $covoits */
        $covoits = $this->get('knp_paginator')->paginate($query, $page, 20);

        $search = new Search();
        $search->startCity = $em->getRepository('EtuCoreBundle:City')->find(749);

        $form = $this->createForm(SearchType::class, $search, ['action' => $this->generateUrl('covoiturage_search')]);

        return [
            'pagination' => $covoits,
            'searchForm' => $form->createView(),
            'today' => new \DateTime(),
        ];
    }

    /**
     * @Route("/search/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="covoiturage_search")
     * @Template()
     *
     * @param mixed $page
     */
    public function searchAction(Request $request, $page = 1)
    {
        $this->denyAccessUnlessGranted('ROLE_COVOIT');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $search = new Search();
        $search->startCity = $em->getRepository('EtuCoreBundle:City')->find(749);

        $form = $this->createForm(SearchType::class, $search);
        $pagination = false;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Search covoits
            $query = $this->get('etu.covoit.query_mapper.search')->map($em->createQueryBuilder(), $search)->getQuery();

            $pagination = $this->get('knp_paginator')->paginate($query, $page, 20);
        }

        return [
            'pagination' => $pagination,
            'searchForm' => $form->createView(),
            'today' => new \DateTime(),
        ];
    }

    /**
     * @Route("/{slug}-{id}", requirements={"slug"=".+"}, name="covoiturage_view")
     * @Template()
     *
     * @param mixed $id
     * @param mixed $slug
     */
    public function viewAction(Request $request, $id, $slug)
    {
        $this->denyAccessUnlessGranted('ROLE_COVOIT');

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

        if (!$covoit) {
            throw $this->createNotFoundException('Covoit not found');
        }

        // One URL to rule them all
        if ($slug != $covoit->getStartCity()->getSlug().'-'.$covoit->getEndCity()->getSlug()) {
            return $this->redirect($this->generateUrl('covoiturage_view', [
                'id' => $covoit->getId(),
                'slug' => $covoit->getStartCity()->getSlug().'-'.$covoit->getEndCity()->getSlug(),
            ]), 301);
        }

        $message = new CovoitMessage();
        $message->setAuthor($this->getUser());
        $message->setCovoit($covoit);

        $messageForm = $this->createForm(CovoitMessageType::class, $message);

        $messageForm->handleRequest($request);
        if ($this->isGranted('ROLE_COVOIT_EDIT') && $messageForm->isSubmitted() && $messageForm->isValid()) {
            $em->persist($message);
            $em->flush();

            // Send notifications to subscribers
            $notif = new Notification();

            $notif
                ->setModule('covoit')
                ->setHelper('covoit_new_message')
                ->setAuthorId($this->getUser()->getId())
                ->setEntityType('covoit')
                ->setEntityId($covoit->getId())
                ->addEntity($message);

            $this->getNotificationsSender()->send($notif);

            // Add current user as subscriber
            $this->getSubscriptionsManager()->subscribe($this->getUser(), 'covoit', $covoit->getId());

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'covoit.messages.message_sent',
            ]);

            return $this->redirect($this->generateUrl('covoiturage_view', [
                'id' => $covoit->getId(),
                'slug' => $covoit->getStartCity()->getSlug().'-'.$covoit->getEndCity()->getSlug(),
            ]));
        }

        return [
            'covoit' => $covoit,
            'messageForm' => $messageForm->createView(),
        ];
    }
}
