<?php

namespace Etu\Core\ApiBundle\Controller;

use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/oauth")
 */
class SecurityController extends Controller
{
    /**
     * @Route("/token", name="oauth_token")
     */
    public function tokenAction()
    {
        $this->get('oauth.server')->handleTokenRequest(\OAuth2\Request::createFromGlobals())->send();
        exit;
    }

    /**
     * @Route("/authorize", name="oauth_authorize")
     * @Template()
     */
    public function authorizeAction(Request $sfRequest)
    {
        $server = $this->get('oauth.server');

        $request = \OAuth2\Request::createFromGlobals();
        $response = new \OAuth2\Response();

        if (! $server->validateAuthorizeRequest($request, $response)) {
            $response->send();
            exit;
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $client = $em->getRepository('EtuCoreApiBundle:OauthClient')->findOneBy([ 'clientId' => $request->query('client_id') ]);

        if (! $client) {
            throw $this->createNotFoundException();
        }

        $form = $this->createFormBuilder()
            ->add('accept', 'submit', [ 'label' => 'Oui, accepter', 'attr' => [ 'class' => 'btn btn-primary', 'value' => '1' ] ])
            ->add('cancel', 'submit', [ 'label' => 'Non, annuler', 'attr' => [ 'class' => 'btn btn-default', 'value' => '0' ] ])
            ->getForm();

        if ($sfRequest->getMethod() == 'POST' && $form->submit($sfRequest)->isValid()) {
            $formData = $sfRequest->request->get('form', []);

            $server->handleAuthorizeRequest($request, $response, isset($formData['accept']), $this->getUser()->getId());
            $response->send();
        }

        $user = $em->getRepository('EtuUserBundle:User')->find($client->getUserId());

        return [
            'client' => $client,
            'user' => $user,
            'form' => $form->createView()
        ];
    }
}