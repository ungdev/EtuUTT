<?php

namespace Etu\Core\ApiBundle\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Serializer\Serializer;

class SecurityListener
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @param Registry $doctrine
     * @param Serializer $serializer
     */
    public function __construct(Registry $doctrine, Serializer $serializer)
    {
        $this->doctrine = $doctrine;
        $this->serializer = $serializer;
    }

    /**
     * @param GetResponseEvent $event
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @return bool
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->request = $event->getRequest();

        if (strpos($this->request->attributes->get('_controller'), 'Api\\Resource') !== false) {
            $token = $this->request->headers->get('etu-utt-token', false);

            if (! $token) {
                $token = $this->request->query->get('token', false);
            }

            if (! $token) {
                $event->setResponse($this->createAccessDeniedResponse(403, 'Authentication required, no token provided'));
                $event->stopPropagation();

                return false;
            }

            $access = $this->doctrine->getManager()
                ->getRepository('EtuCoreApiBundle:Access')
                ->findOneByToken($token);

            if (! $access) {
                $event->setResponse($this->createAccessDeniedResponse(403, 'Authentication failed, invalid token'));
                $event->stopPropagation();

                return false;
            }

            $event->getRequest()->attributes->set('access', $access);
        }
    }

    /**
     * @param $status
     * @param bool $message
     * @return Response
     */
    private function createAccessDeniedResponse($status, $message = false)
    {
        $data = [
            'http' => [
                'status' => $status,
                'message' => ($message) ? $message : Response::$statusTexts[$status]
            ]
        ];

        $request = $this->request;
        $format = 'json';

        if ($request->headers->has('Accept')) {
            $format = $request->query->get('Accept');
        } elseif ($request->query->has('format')) {
            $format = $request->query->get('format');
        } else if ($request->headers->has('format')) {
            $format = $request->headers->get('format');
        }

        if (! in_array($format, ['xml', 'json'])) {
            $format = 'json';
        }

        /** @var Serializer $serializer */
        $serializer = $this->serializer;

        $options = ($format == 'json') ? ['json_encode_options' => JSON_PRETTY_PRINT] : [];

        $response = new Response($serializer->encode($data, $format, $options), $status);
        $response->headers->set('Content-Type', 'text/'.$format);

        return $response;
    }
}
