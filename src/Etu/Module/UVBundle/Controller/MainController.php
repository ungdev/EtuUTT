<?php

namespace Etu\Module\UVBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Module\UVBundle\Entity\UV;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/uvs")
 */
class MainController extends Controller
{
	/**
	 * @Route("", name="uvs_index")
	 * @Template()
	 */
	public function indexAction()
	{
		$this->denyAccessUnlessGranted('ROLE_UV');

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		$query = $em->createQueryBuilder()
			->select('uv')
			->from('EtuModuleUVBundle:UV', 'uv')
			->where('uv.code IN(\''.implode('\', \'', $this->getUser()->getUvsList()).'\')')
			->getQuery();

		$query->useResultCache(true, 3600*24);

		/** @var UV[] $my */
		$my = $query->getResult();

		return array(
			'my' => $my
		);
	}

	/**
	 * @Route("/category/{category}/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="uvs_category")
	 * @Template()
	 */
	public function categoryAction($category, $page = 1)
	{
		$this->denyAccessUnlessGranted('ROLE_UV');

		if (! in_array($category, UV::$categories)) {
			throw $this->createNotFoundException('Invalid category');
		}

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		$query = $em->createQueryBuilder()
			->select('u')
			->from('EtuModuleUVBundle:UV', 'u')
			->where('u.category = :category')
			->setParameter('category', $category)
			->orderBy('u.code', 'ASC')
			->getQuery();

		$query->useResultCache(true, 3600*24);

		$pagination = $this->get('knp_paginator')->paginate($query, $page, 20);

		return array(
			'pagination' => $pagination,
			'category' => $category
		);
	}

	/**
	 * @Route("/search/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="uvs_search")
	 * @Template()
	 */
	public function searchAction(Request $request, $page = 1)
	{
		$this->denyAccessUnlessGranted('ROLE_UV');

		if (! $request->query->has('q')) {
			throw $this->createNotFoundException('No term to search provided');
		}

		$term = $request->query->get('q');

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		$query = $em->createQueryBuilder()
			->select('u')
			->from('EtuModuleUVBundle:UV', 'u')
			->where('u.code LIKE :code')
			->orWhere('u.name LIKE :name')
			->setParameter('code', '%'.$term.'%')
			->setParameter('name', '%'.$term.'%')
			->orderBy('u.code', 'ASC')
			->getQuery();

		$query->useResultCache(true, 3600*24);

		$pagination = $this->get('knp_paginator')->paginate($query, $page, 20);

		return array(
			'pagination' => $pagination,
			'term' => $term
		);
	}

	/**
	 * @Route("/goto/{code}", name="uvs_goto")
	 * @Template()
	 */
	public function goToAction($code)
	{
		$this->denyAccessUnlessGranted('ROLE_UV');

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		/** @var UV $uv */
		$uv = $em->getRepository('EtuModuleUVBundle:UV')
			->findOneBy(array('code' => $code));

		if (! $uv) {
			throw $this->createNotFoundException(sprintf('UV for code %s not found', $code));
		}

		return $this->redirect($this->generateUrl('uvs_view', array(
			'slug' => $uv->getSlug(),
			'name' => StringManipulationExtension::slugify($uv->getName())
		)), 301);
	}
}
