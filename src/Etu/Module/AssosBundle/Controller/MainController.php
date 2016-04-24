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

		return array(
			'pagination' => $orgas
		);
	}

	/**
	 * @Route("/elus/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="elus_index")
	 * @Template()
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

		return array(
			'pagination' => $orgas
		);
	}

	/**
	 * @Route("/orgas/{login}", name="orgas_view")
	 * @Template()
	 */
	public function viewAction($login)
	{
		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $orga Organization */
		$orga = $em->getRepository('EtuUserBundle:Organization')->findOneBy(array('login' => $login));

		if (! $orga) {
			throw $this->createNotFoundException('Orga not found');
		}

        $isElus = false;

        if (strpos($orga->getName(), 'Elus') !== false) {
            $isElus = true;
        }

		return array(
			'orga' => $orga,
            'isElus' => $isElus
		);
	}

	/**
	 * @Route("/orgas/{login}/members", name="orgas_members")
	 * @Template()
	 */
	public function membersAction($login)
	{
		$this->denyAccessUnlessGranted('ROLE_ASSOS_MEMBERS');

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $orga Organization */
		$orga = $em->getRepository('EtuUserBundle:Organization')->findOneBy(array('login' => $login));

		if (! $orga) {
			throw $this->createNotFoundException('Orga not found');
		}

		/** @var $memberships Member[] */
		$memberships = $em->createQueryBuilder()
			->select('m, u, o')
			->from('EtuUserBundle:Member', 'm')
			->leftJoin('m.organization', 'o')
			->leftJoin('m.user', 'u')
			->where('o.login = :orga')
			->setParameter('orga', $login)
			->orderBy('m.role', 'DESC')
			->setMaxResults(50)
			->getQuery()
			->getResult();

		$office = array();
		$members = array();

		foreach ($memberships as $membership) {
			if ($membership->isFromBureau()) {
				$office[] = $membership;
			} else {
				$members[] = $membership;
			}
		}

		return array(
			'orga' => $orga,
			'office' => $office,
			'members' => $members
		);
	}
}
