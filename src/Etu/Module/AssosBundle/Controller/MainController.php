<?php

namespace Etu\Module\AssosBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\Member;
use Etu\Core\UserBundle\Entity\Organization;
// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
    /**
     * @Route("/orgas/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="orgas_index")
     * @Template()
     *
     * @param mixed $page
     */
    public function indexAction($page)
    {
        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('a, p')
            ->from('EtuUserBundle:Organization', 'a')
            ->leftJoin('a.president', 'p')
            ->where('a.name NOT LIKE \'Elus%\'')
            ->orderBy('a.name')
            ->getQuery();

        $orgas = $this->get('knp_paginator')->paginate($query, $page, 10);

        return [
            'pagination' => $orgas,
        ];
    }

    /**
     * @Route("/elus/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="elus_index")
     * @Template()
     *
     * @param mixed $page
     */
    public function elusAction($page)
    {
        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('a, p')
            ->from('EtuUserBundle:Organization', 'a')
            ->leftJoin('a.president', 'p')
            ->where('a.name LIKE \'Elus%\'')
            ->orderBy('a.name')
            ->getQuery();

        $orgas = $this->get('knp_paginator')->paginate($query, $page, 10);

        return [
            'pagination' => $orgas,
        ];
    }

    /**
     * @Route("/orgas/{login}", name="orgas_view")
     * @Template()
     *
     * @param mixed $login
     */
    public function viewAction($login)
    {
        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $orga Organization */
        $orga = $em->getRepository('EtuUserBundle:Organization')->findOneBy(['login' => $login]);

        if (!$orga) {
            throw $this->createNotFoundException('Orga not found');
        }

        $isElus = false;

        if (false !== mb_strpos($orga->getName(), 'Elus')) {
            $isElus = true;
        }

        // Get wiki rights
        $rights = null;
        $modulesManager = $this->get('etu.core.modules_manager');
        if ($modulesManager->getModuleByIdentifier('events')->isEnabled()) {
            $rights = $this->get('etu.wiki.permissions_checker');
        }

        $members = $orga->getMemberships();
        $presidents = [];

        /** @var $member Member */
        foreach ($members as $member) {
            if (Member::ROLE_PRESIDENT == $member->getRole()) {
                if ('Bureau' == $member->getGroup()->getName()) {
                    $presidents[] = $member->getUser();
                }
            }
        }

        return [
            'orga' => $orga,
            'presidents' => $presidents,
            'isElus' => $isElus,
            'wikirights' => $rights,
        ];
    }

    /**
     * @Route("/orgas/{login}/members", name="orgas_members")
     * @Template()
     *
     * @param mixed $login
     */
    public function membersAction($login)
    {
        $this->denyAccessUnlessGranted('ROLE_ASSOS_MEMBERS');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $orga Organization */
        $orga = $em->getRepository('EtuUserBundle:Organization')->findOneBy(['login' => $login]);

        if (!$orga) {
            throw $this->createNotFoundException('Orga not found');
        }

        // Get wiki rights
        $rights = null;
        $modulesManager = $this->get('etu.core.modules_manager');
        if ($modulesManager->getModuleByIdentifier('events')->isEnabled()) {
            $rights = $this->get('etu.wiki.permissions_checker');
        }

        return [
            'orga' => $orga,
            'wikirights' => $rights,
        ];
    }
}
