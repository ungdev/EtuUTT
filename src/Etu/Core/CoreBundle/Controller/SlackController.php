<?php

namespace Etu\Core\CoreBundle\Controller;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use http\Env\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SlackController extends Controller
{
    /**
     * @Route("/slack/{token}", name="slack_endpoint")
     *
     * @param Request $request
     * @param string  $token
     *
     * @return Response
     */
    public function slackEndpoint(Request $request, string $token)
    {
        if ($token != $this->container->getParameter('slack_token')) {
            throw $this->createAccessDeniedException('');
        }
        $data = json_decode($request->getBody());
        $objetEtID = explode('_', $data['actions'][0]['block_id']);
        if ($objetEtID[0] === 'comment') {
            $em = $this->getDoctrine()->getManager();
            $comment = $em->getRepository('EtuModuleUVBundle:Comment')->find($objetEtID[1]);
            if (empty($comment)) {
                throw $this->createNotFoundException();
            }
            if ($data['actions'][0]['action_id'] === 'ok') {
                $comment->setValide(true);
                $em->persist($comment);
                $em->flush();
                // Notify subscribers
                $notif = new Notification();

                $notif
                    ->setModule('uv')
                    ->setHelper('uv_new_comment')
                    ->setAuthorId($comment->getUser()->getId())
                    ->setEntityType('uv')
                    ->setEntityId($comment->getId())
                    ->addEntity($comment);

                $this->getNotificationsSender()->send($notif);

                return new Response(json_encode(['delete_original' => true]));
            } elseif ($data['actions'][0]['action_id'] === 'delete') {
                $em->remove($comment);
                $em->flush();

                return new Response(json_encode(['delete_original' => true]));
            }
        }

        throw $this->createNotFoundException();
    }
}
