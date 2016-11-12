<?php

namespace Etu\Module\CovoitBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\CovoitBundle\Entity\CovoitAlert;
use Etu\Module\CovoitBundle\Form\CovoitAlertType;
use Symfony\Component\HttpFoundation\Request;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/covoiturage/private/alerts")
 * @Template()
 */
class AlertsController extends Controller
{
    /**
     * @Route("/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="covoiturage_my_alerts")
     * @Template()
     */
    public function indexAction($page = 1)
    {
        $this->denyAccessUnlessGranted('ROLE_COVOIT_EDIT');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('a, u')
            ->from('EtuModuleCovoitBundle:CovoitAlert', 'a')
            ->leftJoin('a.user', 'u')
            ->where('u.id = :user')
            ->setParameter('user', $this->getUser()->getId())
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery();

        /** @var CovoitAlert[] $covoits */
        $covoits = $this->get('knp_paginator')->paginate($query, $page, 30);

        return [
            'pagination' => $covoits,
            'today' => new \DateTime(),
        ];
    }

    /**
     * @Route("/create", name="covoiturage_my_alerts_create")
     * @Template()
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_COVOIT_EDIT');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $alert = new CovoitAlert();
        $alert->setUser($this->getUser());

        $form = $this->createForm(CovoitAlertType::class, $alert);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($alert);
            $em->flush();

            // Add current user as subscriber of the specific alert
            $this->getSubscriptionsManager()->subscribe($this->getUser(), 'covoit-alert', $alert->getId());

            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'success',
                'message' => 'covoit.messages.alert_created',
            ));

            return $this->redirect($this->generateUrl('covoiturage_my_alerts'));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}/edit", name="covoiturage_my_alerts_edit")
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_COVOIT_EDIT');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $alert = $em->getRepository('EtuModuleCovoitBundle:CovoitAlert')->find($id);

        $form = $this->createForm(CovoitAlertType::class, $alert);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($alert);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', array(
                    'type' => 'success',
                    'message' => 'covoit.messages.alert_edited',
                ));

            return $this->redirect($this->generateUrl('covoiturage_my_alerts'));
        }

        return [
            'form' => $form->createView(),
            'alert' => $alert,
        ];
    }

    /**
     * @Route("/{id}/delete", name="covoiturage_my_alerts_delete")
     */
    public function deleteAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_COVOIT_EDIT');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $alert = $em->getRepository('EtuModuleCovoitBundle:CovoitAlert')->find($id);

        // Add current user as subscriber of the specific alert
        $this->getSubscriptionsManager()->unsubscribe($this->getUser(), 'covoit-alert', $alert->getId());

        $em->remove($alert);
        $em->flush();

        $this->get('session')->getFlashBag()->set('message', array(
            'type' => 'success',
            'message' => 'covoit.messages.alert_deleted',
        ));

        return $this->redirect($this->generateUrl('covoiturage_my_alerts'));
    }
}
