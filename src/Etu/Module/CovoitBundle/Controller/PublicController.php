<?php

namespace Etu\Module\CovoitBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
        return array();
    }

    /**
     * @Route("/{slug}-{id}", requirements={"slug"=".+"}, name="covoiturage_view")
     * @Template()
     */
    public function viewAction($id)
    {
        return array();
    }
}
