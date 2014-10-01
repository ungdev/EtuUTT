<?php

namespace Etu\Core\ApiBundle\Listener;

use Doctrine\Common\Annotations\Reader;
use Etu\Core\ApiBundle\Oauth\ResponseHandler;
use OAuth2\Server as OAuthServer;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class SecurityListener
{
    /**
     * @var OAuthServer
     */
    protected $server;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var ResponseHandler
     */
    protected $responseHandler;

    /**
     * @param OAuthServer $server
     * @param Reader $reader
     * @param ResponseHandler $responseHandler
     */
    public function __construct(OAuthServer $server, Reader $reader, ResponseHandler $responseHandler)
    {
        $this->server = $server;
        $this->reader = $reader;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @param GetResponseEvent $event
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @return bool
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (strpos($event->getRequest()->attributes->get('_controller'), 'Api\\Resource') !== false) {
            $controller = explode('::', $event->getRequest()->attributes->get('_controller'));

            $reflection = new \ReflectionMethod($controller[0], $controller[1]);

            $scopeAnnotation = $this->reader->getMethodAnnotation($reflection, 'Etu\\Core\\ApiBundle\\Framework\\Annotation\\Scope');

            if ($scopeAnnotation) {
                $scope = $scopeAnnotation->value;
            } else {
                $scope = null;
            }

            header('Access-Control-Allow-Origin: *');

            $request = \OAuth2\Request::createFromGlobals();

            if (! $this->server->verifyResourceRequest($request, null, $scope)) {
                $event->setResponse($this->responseHandler->handle($event->getRequest(), $this->server->getResponse()));
            } else {
                $event->getRequest()->attributes->set('_token', $this->server->getAccessTokenData($request));
            }
        }
    }
}
