<?php

namespace Etu\Module\DailymailBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
    /**
     * @Route("/daymail", name="user_daymail")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
}
