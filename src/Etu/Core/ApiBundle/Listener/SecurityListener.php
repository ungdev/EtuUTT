<?php

namespace Etu\Core\ApiBundle\Listener;

use OAuth2\Server as OAuthServer;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class SecurityListener
{
    /**
     * @var OAuthServer
     */
    protected $server;

    /**
     * @param OAuthServer $server
     */
    public function __construct(OAuthServer $server)
    {
        $this->server = $server;
    }

    /**
     * @param GetResponseEvent $event
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @return bool
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (strpos($event->getRequest()->attributes->get('_controller'), 'Api\\Resource') !== false) {
            if (! $this->server->verifyResourceRequest(\OAuth2\Request::createFromGlobals())) {
                $this->server->getResponse()->send();
                exit;
            }
        }
    }
}
