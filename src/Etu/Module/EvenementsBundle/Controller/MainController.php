<?php

namespace Etu\Module\EvenementsBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
    /**
     * @Route("/events", name="events_index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
}
