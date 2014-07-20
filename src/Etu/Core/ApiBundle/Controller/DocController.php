<?php

namespace Etu\Core\ApiBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import @Route() and @Template() annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/doc")
 */
class DocController extends Controller
{
    /**
     * @Route("", name="devs_doc_index")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/rest/installation", name="devs_doc_rest_installation")
     * @Template()
     */
    public function restInstallationAction()
    {
        return [];
    }

    /**
     * @Route("/rest/usage", name="devs_doc_rest_usage")
     * @Template()
     */
    public function restUsageAction()
    {
        return [];
    }

    /**
     * @Route("/library/phputt", name="devs_doc_lib_phputt")
     * @Template()
     */
    public function libPhputtAction()
    {
        return [];
    }
}
