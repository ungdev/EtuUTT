<?php

namespace Etu\Module\CovoitBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\CovoitBundle\Entity\Covoit;
use Etu\Module\CovoitBundle\Entity\CovoitMessage;
use Etu\Module\CovoitBundle\Entity\CovoitSubscription;
use Symfony\Component\HttpFoundation\Request;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/covoiturage/private")
 * @Template()
 */
class PrivateController extends Controller
{
    /**
     * @Route("/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="covoiturage_my_index")
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
            ->where('c.author = :user')
            ->orWhere('cs.user = :user')
            ->setParameter('user', $this->getUser()->getId())
            ->orderBy('c.date', 'DESC')
            ->getQuery();

        /** @var Covoit[] $covoits */
        $covoits = $this->get('knp_paginator')->paginate($query, $page, 30);

        return [
            'pagination' => $covoits,
            'today' => new \DateTime()
        ];
    }

    /**
     * @Route("/propose", name="covoiturage_my_propose")
     * @Template()
     */
    public function proposeAction(Request $request)
    {
        if (! $this->getUserLayer()->isUser()) {
            return $this->createAccessDeniedResponse();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $proposal = new Covoit();
        $proposal->setAuthor($this->getUser());
        $proposal->setStartCity($em->getRepository('EtuCoreBundle:City')->find(749)); // Troyes
        $proposal->setEndCity($em->getRepository('EtuCoreBundle:City')->find(826)); // Paris

        if ($this->getUser()->getPhoneNumber()) {
            $proposal->setPhoneNumber($this->getUser()->getPhoneNumber());
        }

        $form = $this->createForm($this->get('etu.covoit.form.proposal'), $proposal);

        if ($request->getMethod() == 'POST' && $form->submit($request)->isValid()) {
            $proposal->setStartHour($proposal->getStartHour()->format('H:i'));
            $proposal->setEndHour($proposal->getEndHour()->format('H:i'));

            $em->persist($proposal);
            $em->flush();

            // Add current user as subscriber
            $this->getSubscriptionsManager()->subscribe($this->getUser(), 'covoit', $proposal->getId());

            $this->get('session')->getFlashBag()->set('message', array(
                    'type' => 'success',
                    'message' => 'covoit.messages.created'
                ));

            return $this->redirect($this->generateUrl('covoiturage_view', [
                        'id' => $proposal->getId(),
                        'slug' => $proposal->getStartCity()->getSlug() . '-' . $proposal->getEndCity()->getSlug()
                    ]));
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/{id}/edit", name="covoiturage_my_edit")
     * @Template()
     */
    public function editAction(Request $request, $id)
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
            throw $this->createNotFoundException();
        }

        if ($covoit->getAuthor()->getId() != $this->getUser()->getId()) {
            throw new AccessDeniedHttpException();
        }

        $covoit->setStartHour(\DateTime::createFromFormat('H:i', $covoit->getStartHour()));
        $covoit->setEndHour(\DateTime::createFromFormat('H:i', $covoit->getEndHour()));

        $form = $this->createForm($this->get('etu.covoit.form.proposal'), $covoit);

        if ($request->getMethod() == 'POST' && $form->submit($request)->isValid()) {
            $covoit->setStartHour($covoit->getStartHour()->format('H:i'));
            $covoit->setEndHour($covoit->getEndHour()->format('H:i'));

            $em->persist($covoit);
            $em->flush();

            // Send notifications to subscribers
            $notif = new Notification();

            $notif
                ->setModule($this->getCurrentBundle()->getIdentifier())
                ->setHelper('covoit_edited')
                ->setAuthorId($this->getUser()->getId())
                ->setEntityType('covoit')
                ->setEntityId($covoit->getId())
                ->addEntity($covoit);

            $this->getNotificationsSender()->send($notif);

            // Flash message
            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'success',
                'message' => 'covoit.messages.edited'
            ));

            return $this->redirect($this->generateUrl('covoiturage_view', [
                    'id' => $covoit->getId(),
                    'slug' => $covoit->getStartCity()->getSlug() . '-' . $covoit->getEndCity()->getSlug()
                ]));
        }

        return [
            'form' => $form->createView(),
            'covoit' => $covoit,
        ];
    }

    /**
     * @Route("/edit/message/{id}", defaults={"id" = 1}, requirements={"id" = "\d+"}, name="covoiturage_my_edit_message")
     * @Template()
     */
    public function editMessageAction(Request $request, CovoitMessage $message)
    {
        if (! $this->getUserLayer()->isUser()) {
            return $this->createAccessDeniedResponse();
        }

        if ($message->getAuthor()->getId() != $this->getUser()->getId()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createForm($this->get('etu.covoit.form.message'), $message);

        if ($request->getMethod() == 'POST' && $form->submit($request)->isValid()) {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();

            $em->persist($message);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'success',
                'message' => 'covoit.messages.message_edited'
            ));

            return $this->redirect($this->generateUrl('covoiturage_view', [
                'id' => $message->getCovoit()->getId(),
                'slug' => $message->getCovoit()->getStartCity()->getSlug() . '-' . $message->getCovoit()->getEndCity()->getSlug()
            ]));
        }

        return [
            'form' => $form->createView(),
            'covoitMessage' => $message
        ];
    }

    /**
     * @Route("/{id}/subscribe", name="covoiturage_my_subscribe")
     */
    public function subscribeAction($id)
    {
        if (! $this->getUserLayer()->isUser()) {
            return $this->createAccessDeniedResponse();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Covoit $covoit */
        $covoit = $em->createQueryBuilder()
            ->select('c, s, u')
            ->from('EtuModuleCovoitBundle:Covoit', 'c')
            ->leftJoin('c.subscriptions', 's')
            ->leftJoin('s.user', 'u')
            ->where('c.id = :id')
            ->setParameter('id', (int) $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (! $covoit) {
            throw $this->createNotFoundException();
        }

        if ($covoit->hasUser($this->getUser())) {
            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'error',
                'message' => 'covoit.messages.already_subscribed'
            ));
        } else {
            $subscription = new CovoitSubscription();
            $subscription->setCovoit($covoit);
            $subscription->setUser($this->getUser());

            if ($this->getUser()->getPhoneNumber()) {
                $subscription->setPhoneNumber($this->getUser()->getPhoneNumber());
            }

            $covoit->addSubscription($subscription);

            $em->persist($subscription);
            $em->persist($covoit);
            $em->flush();

            // Add current user as subscriber
            $this->getSubscriptionsManager()->subscribe($this->getUser(), 'covoit', $covoit->getId());

            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'success',
                'message' => 'covoit.messages.subscribed'
            ));
        }

        return $this->redirect($this->generateUrl('covoiturage_view', [
            'id' => $covoit->getId(),
            'slug' => $covoit->getStartCity()->getSlug() . '-' . $covoit->getEndCity()->getSlug()
        ]));
    }
}
