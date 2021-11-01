<?php

namespace Etu\Core\CoreBundle\Controller;

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
            return new Response('Incroyable', 200);
        }
        $data = json_decode($request->getBody());
        $objetEtID = explode('_', $data['actions'][0]['block_id']);
        if ('comment' === $objetEtID[0]) {
            $em = $this->getDoctrine()->getManager();
            $comment = $em->getRepository('EtuModuleUVBundle:Comment')->find($objetEtID[1]);
            if (empty($comment)) {
                throw $this->createNotFoundException();
            }
            if ('ok' === $data['actions'][0]['action_id']) {
                $comment->setIsValide(true);
                $em->persist($comment);
                $em->flush();

                return new Response(json_encode(['delete_original' => true]));
            } elseif ('delete' === $data['actions'][0]['action_id']) {
                $em->remove($comment);
                $em->flush();

                return new Response(json_encode(['delete_original' => true]));
            }
        }

        return new Response('Not found', 200);
    }
}
