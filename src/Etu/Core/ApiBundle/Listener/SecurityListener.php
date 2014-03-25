<?php

namespace Etu\Core\ApiBundle\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SecurityListener
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param GetResponseEvent $event
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (strpos($event->getRequest()->attributes->get('_controller'), 'ApiBundle') !== false) {
            $token = $event->getRequest()->headers->get('etu-utt-token', false);

            if (! $token) {
                $token = $event->getRequest()->query->get('token', false);
            }

            if (! $token) {
                $event->setResponse(new Response('Authentication required', 401));
            }

            $access = $this->doctrine->getManager()
                ->getRepository('EtuCoreApiBundle:Access')
                ->findOneByToken($token);

            if (! $access) {
                $event->setResponse(new Response('Invalid token', 403));
            }

            $event->getRequest()->attributes->set('access', $access);
        }
    }
}
