<?php

namespace Etu\Module\UVBundle\Controller;

use Doctrine\ORM\EntityManager;

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

