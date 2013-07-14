<?php

namespace Etu\Module\UVBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Module\UVBundle\Entity\UV;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

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
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		/** @var UV[] $my */
		$my = $em->createQueryBuilder()
			->select('uv')
			->from('EtuModuleUVBundle:UV', 'uv')
			->where('uv.code IN(\''.implode('\', \'', $this->getUser()->getUvsList()).'\')')
			->getQuery()
			->getResult();

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
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

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
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

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

		$pagination = $this->get('knp_paginator')->paginate($query, $page, 20);

		return array(
			'pagination' => $pagination,
			'term' => $term
		);
	}

	/**
	 * @Route("/{slug}-{name}", name="uvs_view")
	 * @Template()
	 */
	public function viewAction($slug, $name)
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		/** @var UV $uv */
		$uv = $em->getRepository('EtuModuleUVBundle:UV')
			->findOneBy(array('slug' => $slug));

		if (! $uv) {
			throw $this->createNotFoundException(sprintf('UV for slug %s not found', $slug));
		}

		if (StringManipulationExtension::slugify($uv->getName()) != $name) {
			return $this->redirect($this->generateUrl('uvs_view', array(
				'slug' => $uv->getSlug(), 'name' => StringManipulationExtension::slugify($uv->getName())
			)), 301);
		}

		return array(
			'uv' => $uv
		);
	}
}

