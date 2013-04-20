<?php

namespace Etu\Module\AssosBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Etu\Core\UserBundle\Entity\Organization;
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

		return array(
			'orga' => $orga
		);
	}
}
