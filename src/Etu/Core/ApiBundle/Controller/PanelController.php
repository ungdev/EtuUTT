<?php

namespace Etu\Core\ApiBundle\Controller;

use Etu\Core\ApiBundle\Entity\OauthClient;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/panel")
 */
class PanelController extends Controller
{
    /**
     * @Route("", name="devs_panel_index")
     * @Template()
     */
    public function indexAction()
    {
        if (! $this->getUserLayer()->isUser()) {
            return $this->createAccessDeniedResponse();
        }

        $em = $this->getDoctrine()->getManager();

        /** @var OauthClient[] $clients */
        $clients = $em->getRepository('EtuCoreApiBundle:OauthClient')->findBy([ 'userId' => $this->getUser()->getId() ]);

        return [
            'clients' => $clients
        ];
    }
}