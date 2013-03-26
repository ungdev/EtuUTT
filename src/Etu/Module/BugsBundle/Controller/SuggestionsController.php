<?php

namespace Etu\Module\BugsBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class SuggestionsController
 * @package Etu\Module\BugsBundle\Controller
 *
 * @Route("/suggestions")
 */
class SuggestionsController extends Controller
{
    /**
     * @Route("/", name="bugs_suggestions")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
}
