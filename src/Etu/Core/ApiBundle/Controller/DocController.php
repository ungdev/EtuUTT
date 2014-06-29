<?php

namespace Etu\Core\ApiBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import @Route() and @Template() annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DocController extends Controller
{
    /**
     * @Route("", name="doc_index")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/rest/installation", name="doc_rest_installation")
     * @Template()
     */
    public function restInstallationAction()
    {
        return [];
    }

    /**
     * @Route("/rest/usage", name="doc_rest_usage")
     * @Template()
     */
    public function restUsageAction()
    {
        return [];
    }

    /**
     * @Route("/library/phputt", name="doc_lib_phputt")
     * @Template()
     */
    public function libPhputtAction()
    {
        return [];
    }
}
