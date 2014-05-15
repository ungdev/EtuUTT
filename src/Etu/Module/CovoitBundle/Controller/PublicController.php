<?php

namespace Etu\Module\CovoitBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\CovoitBundle\Entity\Covoit;
use Etu\Module\CovoitBundle\Entity\CovoitMessage;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/carpooling")
 * @Template()
 */
class PublicController extends Controller
{
    /**
     * @Route("", name="covoiturage_index")
     * @Template()
     */
    public function indexAction()
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
        $covoits = $this->get('knp_paginator')->paginate($query, $page, 40);

        return [
            'pagination' => $covoits,
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
