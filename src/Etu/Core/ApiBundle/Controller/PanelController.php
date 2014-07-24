<?php

namespace Etu\Core\ApiBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Entity\OauthClient;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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

            $this->get('session')->getFlashBag()->set('message', array(
                    'type' => 'success',
                    'message' => 'Votre application a bien été crée'
                ));

            return $this->redirect($this->generateUrl('devs_panel_index'));
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/app/manage/{id}", name="devs_panel_manage_app")
     * @Template()
     */
    public function manageAppAction(Request $request, $id)
    {
        if (! $this->getUserLayer()->isUser()) {
            return $this->createAccessDeniedResponse();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $client = $em->getRepository('EtuCoreApiBundle:OauthClient')->findOneBy([
            'clientId' => $id
        ]);

        if (! $client) {
            throw $this->createNotFoundException();
        }

        if ($client->getUserId() != $this->getUser()->getId()) {
            throw new AccessDeniedHttpException();
        }

        $client->deductScopesList();

        $form = $this->createForm($this->get('etu.api.form.client'), $client);

        if ($request->getMethod() == 'POST' && $form->submit($request)->isValid()) {
            $client->injectScopesList();

            $em->persist($client);
            $em->flush();

            $client->upload();

            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'success',
                'message' => 'Votre application a bien été modifiée'
            ));

            return $this->redirect($this->generateUrl('devs_panel_manage_app', [ 'id' => $id ]));
        }

        return [
            'form' => $form->createView()
        ];
    }
}