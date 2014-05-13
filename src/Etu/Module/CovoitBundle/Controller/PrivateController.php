<?php

namespace Etu\Module\CovoitBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\CovoitBundle\Entity\Covoit;

// Import annotations
use Etu\Module\CovoitBundle\Model\Proposal;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/carpooling/my-proposals")
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
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('c, cs, sub')
            ->from('EtuModuleCovoitBundle:Covoit', 'c')
            ->leftJoin('c.steps', 'cs')
            ->leftJoin('cs.sbuscriptions', 'sub')
            ->where('c.author = :user')
            ->orWhere('sub.user = :user')
            ->setParameter('user', $this->getUser()->getId())
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery();

        /** @var Covoit[] $covoits */
        $covoits = $this->get('knp_paginator')->paginate($query, $page, 40);

        return [
            'covoits' => $covoits,
        ];
    }

    /**
     * @Route("/propose", name="covoiturage_my_propose")
     * @Template()
     */
    public function proposeAction(Request $request)
    {
        $proposal = new Proposal();

        if ($this->getUser()->getPhoneNumber()) {
            $proposal->phoneNumber = $this->getUser()->getPhoneNumber();
        }

        $form = $this->createForm($this->get('covoit.form.proposal'), $proposal);

        if ($request->getMethod() == 'POST' && $form->submit($request)->isValid()) {
            var_dump($proposal);
            exit;
        }

        return [
            'form' => $form->createView()
        ];
    }
}
