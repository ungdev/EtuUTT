<?php

namespace Etu\Core\ApiBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Entity\OauthClient;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Validator\Constraints\EqualTo;

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
        $clients = $em->getRepository('EtuCoreApiBundle:OauthClient')->findBy([ 'user' => $this->getUser() ]);

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
        $client->setUser($this->getUser());

        $defaultScopes = $em->getRepository('EtuCoreApiBundle:OauthScope')->findBy([ 'isDefault' => true ]);

        foreach ($defaultScopes as $defaultScope) {
            $client->addScope($defaultScope);
        }

        $form = $this->createForm($this->get('etu.api.form.client'), $client);

        if ($request->getMethod() == 'POST' && $form->submit($request)->isValid()) {
            $client->generateClientId();
            $client->generateClientSecret();

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
     * @Route("/app/manage/{clientId}", name="devs_panel_manage_app")
     * @Template()
     */
    public function manageAppAction(OauthClient $client)
    {
        if (! $this->getUserLayer()->isUser()) {
            return $this->createAccessDeniedResponse();
        }

        if ($client->getUser()->getId() != $this->getUser()->getId()) {
            throw new AccessDeniedHttpException();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var OauthClient[] $clients */
        $clients = $em->getRepository('EtuCoreApiBundle:OauthClient')->findBy([ 'user' => $this->getUser() ]);

        return [
            'client' => $client,
            'clients' => $clients,
        ];
    }

    /**
     * @Route("/app/manage/{clientId}/edit", name="devs_panel_edit_app")
     * @Template()
     */
    public function editAppAction(Request $request, OauthClient $client)
    {
        if (! $this->getUserLayer()->isUser()) {
            return $this->createAccessDeniedResponse();
        }

        if ($client->getUser()->getId() != $this->getUser()->getId()) {
            throw new AccessDeniedHttpException();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm($this->get('etu.api.form.client'), $client);

        if ($request->getMethod() == 'POST' && $form->submit($request)->isValid()) {
            $em->persist($client);
            $em->flush();

            $client->upload();

            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'success',
                'message' => 'Votre application a bien été modifiée'
            ));

            return $this->redirect($this->generateUrl('devs_panel_manage_app', [ 'clientId' => $client->getClientId() ]));
        }

        /** @var OauthClient[] $clients */
        $clients = $em->getRepository('EtuCoreApiBundle:OauthClient')->findBy([ 'user' => $this->getUser() ]);

        return [
            'client' => $client,
            'clients' => $clients,
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/app/manage/{clientId}/remove", name="devs_panel_remove_app")
     * @Template()
     */
    public function removeAppAction(Request $request, OauthClient $client)
    {
        if (! $this->getUserLayer()->isUser()) {
            return $this->createAccessDeniedResponse();
        }

        if ($client->getUser()->getId() != $this->getUser()->getId()) {
            throw new AccessDeniedHttpException();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
            ->add('client_id', 'text', [
                'required' => true,
                'label' => 'Par sécurité, vous devez entrer le Client ID de cette application pour pouvoir la supprimer :',
                'constraints' => new EqualTo([ 'value' => (string) $client->getClientId(), 'message' => 'Ce Client ID n\'est pas correct'])
            ])
            ->getForm();

        if ($request->getMethod() == 'POST' && $form->submit($request)->isValid()) {
            $em->remove($client);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'success',
                'message' => 'L\' application ' . $client->getName() . ' a bien été supprimée'
            ));

            return $this->redirect($this->generateUrl('devs_panel_index'));
        }

        /** @var OauthClient[] $clients */
        $clients = $em->getRepository('EtuCoreApiBundle:OauthClient')->findBy([ 'user' => $this->getUser() ]);

        return [
            'client' => $client,
            'clients' => $clients,
            'form' => $form->createView()
        ];
    }
}