<?php

namespace Etu\Core\ApiBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Entity\OauthClient;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

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

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var OauthClient[] $clients */
        $clients = $em->getRepository('EtuCoreApiBundle:OauthClient')->findBy([ 'userId' => $this->getUser()->getId() ]);

        return [
            'clients' => $clients
        ];
    }

    /**
     * @Route("/app/create", name="devs_panel_create_app")
     * @Template()
     */
    public function createAppAction(Request $request)
    {
        if (! $this->getUserLayer()->isUser()) {
            return $this->createAccessDeniedResponse();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $client = new OauthClient();
        $client->setUserId($this->getUser()->getId());

        $form = $this->createForm($this->get('etu.api.form.client'), $client);

        if ($request->getMethod() == 'POST' && $form->submit($request)->isValid()) {
            $client->generateClientId();
            $client->generateClientSecret();
            $client->injectScopesList();

            $em->persist($client);
            $em->flush();

            $client->upload();
        }

        return [
            'form' => $form->createView()
        ];
    }
}