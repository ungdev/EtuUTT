<?php

namespace Etu\Module\UVBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;

use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Module\UVBundle\Entity\UV;
use Symfony\Component\HttpFoundation\Response;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/uvs")
 */
class MainController extends Controller
{
	/**
	 * @Route ("", name="uvs_index")
	 * @Template()
	 */
	public function indexAction()
	{

	}
}

