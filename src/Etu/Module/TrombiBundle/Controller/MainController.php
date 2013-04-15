<?php

namespace Etu\Module\TrombiBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/trombi")
 */
class MainController extends Controller
{
    /**
     * @Route("/", name="trombi_index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
}
