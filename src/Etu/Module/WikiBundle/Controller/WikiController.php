<?php

namespace Etu\Module\WikiBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class WikiController
 * @package Etu\Module\WikiBundle\Controller
 *
 * @Route("/wiki")
 */
class WikiController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function homepageAction()
    {
        return array(

        );
    }
}
