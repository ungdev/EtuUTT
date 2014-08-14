<?php

namespace Etu\Core\ApiBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Entity\OauthClient;
use Etu\Core\ApiBundle\Entity\OauthScope;
use Etu\Core\ApiBundle\Entity\StatLogin;
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
    public function manageAppAction($id)
    {
        if (! $this->getUserLayer()->isUser()) {
            return $this->createAccessDeniedResponse();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $client = $em->getRepository('EtuCoreApiBundle:OauthClient')->findOneBy([ 'clientId' => $id ]);

        if (! $client) {
            throw $this->createNotFoundException();
        }

        if ($client->getUserId() != $this->getUser()->getId()) {
            throw new AccessDeniedHttpException();
        }

        /** @var OauthClient[] $clients */
        $clients = $em->getRepository('EtuCoreApiBundle:OauthClient')->findBy([ 'userId' => $this->getUser()->getId() ]);

        $qb = $em->getRepository('EtuCoreApiBundle:OauthScope')->createQueryBuilder('s');

        $scopes = $qb->orderBy('s.weight', 'ASC')->where($qb->expr()->in('s.scope', $client->getScopeList()))->getQuery()->getResult();

        $date = ((new \DateTime('first day of this month'))->setTime(0, 0, 0));

        /** @var StatLogin[] $stats */
        $stats = $em->createQueryBuilder()
            ->select('l')
            ->from('EtuCoreApiBundle:StatLogin', 'l')
            ->where('l.client = :client')
            ->andWhere('l.date >= :date')
            ->setParameter('client', $client->getId())
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();

        $days = array_fill_keys(range(1, date('t', gmmktime(0, 0, 0, (int) date('m'), 1))), 0);

        foreach ($stats as $stat) {
            if (! isset($days[(int) $stat->getDate()->format('d')])) {
                $days[(int) $stat->getDate()->format('d')] = 0;
            }

            $days[(int) $stat->getDate()->format('d')]++;
        }

        $jsonStats = [];

        foreach ($days as $day => $count) {
            $jsonStats[] = '["' . str_pad($day, 2, 0, STR_PAD_LEFT) . '", ' . $count . ']';
        }

        return [
            'client' => $client,
            'clients' => $clients,
            'scopes' => $scopes,
            'scopesDescs' => OauthScope::$descDev,
            'stats' => implode(', ', $jsonStats),
        ];
    }

    /**
     * @Route("/app/manage/{id}/edit", name="devs_panel_edit_app")
     * @Template()
     */
    public function editAppAction(Request $request, $id)
    {
        if (! $this->getUserLayer()->isUser()) {
            return $this->createAccessDeniedResponse();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $client = $em->getRepository('EtuCoreApiBundle:OauthClient')->findOneBy([ 'clientId' => $id ]);

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

        /** @var OauthClient[] $clients */
        $clients = $em->getRepository('EtuCoreApiBundle:OauthClient')->findBy([ 'userId' => $this->getUser()->getId() ]);

        return [
            'client' => $client,
            'clients' => $clients,
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/app/manage/{id}/remove", name="devs_panel_remove_app")
     * @Template()
     */
    public function removeAppAction(Request $request, $id)
    {
        if (! $this->getUserLayer()->isUser()) {
            return $this->createAccessDeniedResponse();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $client = $em->getRepository('EtuCoreApiBundle:OauthClient')->findOneBy([ 'clientId' => $id ]);

        if (! $client) {
            throw $this->createNotFoundException();
        }

        if ($client->getUserId() != $this->getUser()->getId()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createFormBuilder()
            ->add('client_id', 'text', [
                'required' => true,
                'label' => 'Par sécurité, vous devez entrer le Client ID de cette application pour pouvoir la supprimer :',
                'constraints' => new EqualTo([ 'value' => (string) $client->getClientId(), 'message' => 'Ce Client ID n\'est pas correct'])
            ])
            ->getForm();

        if ($request->getMethod() == 'POST' && $form->handleRequest($request)->isValid()) {
            $em->remove($client);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'success',
                'message' => 'L\' application ' . $client->getName() . ' a bien été supprimée'
            ));

            return $this->redirect($this->generateUrl('devs_panel_index'));
        }

        /** @var OauthClient[] $clients */
        $clients = $em->getRepository('EtuCoreApiBundle:OauthClient')->findBy([ 'userId' => $this->getUser()->getId() ]);

        return [
            'client' => $client,
            'clients' => $clients,
            'form' => $form->createView()
        ];
    }
}