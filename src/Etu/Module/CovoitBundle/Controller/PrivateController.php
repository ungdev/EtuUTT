<?php

namespace Etu\Module\CovoitBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\CovoitBundle\Entity\Covoit;
use Etu\Module\CovoitBundle\Entity\CovoitMessage;
use Etu\Module\CovoitBundle\Entity\CovoitSubscription;
use Etu\Module\CovoitBundle\Form\CovoitMessageType;
use Etu\Module\CovoitBundle\Form\CovoitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
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
     *
     * @param mixed $page
     */
    public function indexAction($page = 1)
    {
        $this->denyAccessUnlessGranted('ROLE_COVOIT_EDIT');

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
        $covoits = $this->get('knp_paginator')->paginate($query, $page, 20);

        return [
            'pagination' => $covoits,
            'today' => new \DateTime(),
        ];
    }

    /**
     * @Route("/propose", name="covoiturage_my_propose")
     * @Template()
     */
    public function proposeAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_COVOIT_EDIT');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $proposal = new Covoit();
        $proposal->setAuthor($this->getUser());
        $proposal->setStartCity($em->getRepository('EtuCoreBundle:City')->find(749)); // Troyes
        $proposal->setEndCity($em->getRepository('EtuCoreBundle:City')->find(826)); // Paris

        if ($this->getUser()->getPhoneNumber()) {
            $proposal->setPhoneNumber($this->getUser()->getPhoneNumber());
        }

        $form = $this->createForm(CovoitType::class, $proposal);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $proposal->setStartHour($proposal->getStartHour()->format('H:i'));
            $proposal->setEndHour($proposal->getEndHour()->format('H:i'));

            $em->persist($proposal);
            $em->flush();

            // Add current user as subscriber
            $this->getSubscriptionsManager()->subscribe($this->getUser(), 'covoit', $proposal->getId());

            // Dispatch the covoit for alerts
            $this->get('etu.covoit.notifs_dispatcher')->dispatch($proposal);

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'covoit.messages.created',
            ]);

            return $this->redirect($this->generateUrl('covoiturage_view', [
                'id' => $proposal->getId(),
                'slug' => $proposal->getStartCity()->getSlug().'-'.$proposal->getEndCity()->getSlug(),
            ]));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}/edit", name="covoiturage_my_edit")
     * @Template()
     *
     * @param mixed $id
     */
    public function editAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_COVOIT_EDIT');

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
            throw $this->createNotFoundException();
        }

        if ($covoit->getAuthor()->getId() != $this->getUser()->getId()) {
            throw new AccessDeniedHttpException();
        }

        $old = clone $covoit;

        $covoit->setStartHour(\DateTime::createFromFormat('H:i', $covoit->getStartHour()));
        $covoit->setEndHour(\DateTime::createFromFormat('H:i', $covoit->getEndHour()));

        $form = $this->createForm(CovoitType::class, $covoit);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $covoit->setStartHour($covoit->getStartHour()->format('H:i'));
            $covoit->setEndHour($covoit->getEndHour()->format('H:i'));

            $em->persist($covoit);
            $em->flush();

            // Send notifications to subscribers only if the covoit was edited
            $edited = false;

            if ($old->getStartCity()->getId() != $covoit->getStartCity()->getId()) {
                $edited = true;
            } elseif ($old->getEndCity()->getId() != $covoit->getEndCity()->getId()) {
                $edited = true;
            } elseif ($old->getBlablacarUrl() != $covoit->getBlablacarUrl()) {
                $edited = true;
            } elseif ($old->getCapacity() != $covoit->getCapacity()) {
                $edited = true;
            } elseif ($old->getDate() != $covoit->getDate()) {
                $edited = true;
            } elseif ($old->getStartAdress() != $covoit->getStartAdress()) {
                $edited = true;
            } elseif ($old->getStartHour() != $covoit->getStartHour()) {
                $edited = true;
            } elseif ($old->getEndAdress() != $covoit->getEndAdress()) {
                $edited = true;
            } elseif ($old->getEndHour() != $covoit->getEndHour()) {
                $edited = true;
            } elseif ($old->getNotes() != $covoit->getNotes()) {
                $edited = true;
            } elseif ($old->getPhoneNumber() != $covoit->getPhoneNumber()) {
                $edited = true;
            } elseif ($old->getPrice() != $covoit->getPrice()) {
                $edited = true;
            }

            if ($edited) {
                $notif = new Notification();

                $notif
                    ->setModule('covoit')
                    ->setHelper('covoit_edited')
                    ->setAuthorId($this->getUser()->getId())
                    ->setEntityType('covoit')
                    ->setEntityId($covoit->getId())
                    ->addEntity($covoit);

                $this->getNotificationsSender()->send($notif);
            }

            // Flash message
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'covoit.messages.edited',
            ]);

            return $this->redirect($this->generateUrl('covoiturage_view', [
                'id' => $covoit->getId(),
                'slug' => $covoit->getStartCity()->getSlug().'-'.$covoit->getEndCity()->getSlug(),
            ]));
        }

        return [
            'form' => $form->createView(),
            'covoit' => $covoit,
        ];
    }

    /**
     * @Route("/edit/message/{id}", requirements={"id" = "\d+"}, name="covoiturage_my_edit_message")
     * @Template()
     */
    public function editMessageAction(Request $request, CovoitMessage $message)
    {
        $this->denyAccessUnlessGranted('ROLE_COVOIT_EDIT');

        if ($message->getAuthor()->getId() != $this->getUser()->getId()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createForm(CovoitMessageType::class, $message);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();

            $em->persist($message);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'covoit.messages.message_edited',
            ]);

            return $this->redirect($this->generateUrl('covoiturage_view', [
                'id' => $message->getCovoit()->getId(),
                'slug' => $message->getCovoit()->getStartCity()->getSlug().'-'.$message->getCovoit()->getEndCity()->getSlug(),
            ]));
        }

        return [
            'form' => $form->createView(),
            'covoitMessage' => $message,
        ];
    }

    /**
     * @Route("/{id}/cancel/{confirm}", defaults={"confirm" = false}, name="covoiturage_my_cancel")
     * @Template()
     *
     * @param mixed $id
     * @param mixed $confirm
     */
    public function cancelAction($id, $confirm)
    {
        $this->denyAccessUnlessGranted('ROLE_COVOIT_EDIT');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Covoit $covoit */
        $covoit = $em->createQueryBuilder()
            ->select('c, s, e, a')
            ->from('EtuModuleCovoitBundle:Covoit', 'c')
            ->leftJoin('c.author', 'a')
            ->leftJoin('c.startCity', 's')
            ->leftJoin('c.endCity', 'e')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$covoit) {
            throw $this->createNotFoundException('Covoit not found');
        }

        if ($covoit->getAuthor()->getId() != $this->getUser()->getId()) {
            throw new AccessDeniedHttpException();
        }

        if ($confirm) {
            $covoit->getStartCity();
            $covoit->getEndCity();

            $em->remove($covoit);
            $em->flush();

            $notif = new Notification();

            $notif
                ->setModule('covoit')
                ->setHelper('covoit_canceled')
                ->setAuthorId($this->getUser()->getId())
                ->setEntityType('covoit')
                ->setEntityId($covoit->getId())
                ->addEntity($covoit);

            $this->getNotificationsSender()->send($notif);

            // Flash message
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'covoit.messages.canceled',
            ]);

            return $this->redirect($this->generateUrl('covoiturage_my_index'));
        }

        return [
            'covoit' => $covoit,
        ];
    }

    /**
     * @Route("/{id}/subscribe", name="covoiturage_my_subscribe")
     *
     * @param mixed $id
     */
    public function subscribeAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_COVOIT_EDIT');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Covoit $covoit */
        $covoit = $em->createQueryBuilder()
            ->select('c, s, u, sc, ec')
            ->from('EtuModuleCovoitBundle:Covoit', 'c')
            ->leftJoin('c.subscriptions', 's')
            ->leftJoin('c.startCity', 'sc')
            ->leftJoin('c.endCity', 'ec')
            ->leftJoin('s.user', 'u')
            ->where('c.id = :id')
            ->setParameter('id', (int) $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$covoit) {
            throw $this->createNotFoundException();
        }

        if (!$this->getUser()->getPhoneNumber()) {
            $this->get('session')->getFlashBag()->set('message', [
                    'type' => 'error',
                    'message' => 'covoit.messages.required_phone',
                ]);
        } elseif ($covoit->hasUser($this->getUser())) {
            $this->get('session')->getFlashBag()->set('message', [
                    'type' => 'error',
                    'message' => 'covoit.messages.already_subscribed',
                ]);
        } else {
            $subscription = new CovoitSubscription();
            $subscription->setCovoit($covoit);
            $subscription->setUser($this->getUser());
            $subscription->setPhoneNumber($this->getUser()->getPhoneNumber());

            $covoit->addSubscription($subscription);

            $em->persist($subscription);
            $em->persist($covoit);
            $em->flush();

            // Notify followers
            $notif = new Notification();

            $notif
                ->setModule('covoit')
                ->setHelper('covoit_subscription')
                ->setAuthorId($this->getUser()->getId())
                ->setEntityType('covoit')
                ->setEntityId($covoit->getId())
                ->addEntity($subscription);

            $this->getNotificationsSender()->send($notif);

            // Add current user as subscriber
            $this->getSubscriptionsManager()->subscribe($this->getUser(), 'covoit', $covoit->getId());

            $this->get('session')->getFlashBag()->set('message', [
                    'type' => 'success',
                    'message' => 'covoit.messages.subscribed',
                ]);
        }

        return $this->redirect($this->generateUrl('covoiturage_view', [
                    'id' => $covoit->getId(),
                    'slug' => $covoit->getStartCity()->getSlug().'-'.$covoit->getEndCity()->getSlug(),
                ]));
    }

    /**
     * @Route("/{id}/unsubscribe", name="covoiturage_my_unsubscribe")
     */
    public function unsubscribeAction(CovoitSubscription $subscription)
    {
        $this->denyAccessUnlessGranted('ROLE_COVOIT_EDIT');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $em->remove($subscription);
        $em->flush();

        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'covoit.messages.unsubscribed',
        ]);

        return $this->redirect($this->generateUrl('covoiturage_view', [
            'id' => $subscription->getCovoit()->getId(),
            'slug' => $subscription->getCovoit()->getStartCity()->getSlug().'-'.$subscription->getCovoit()->getEndCity()->getSlug(),
        ]));
    }
}
