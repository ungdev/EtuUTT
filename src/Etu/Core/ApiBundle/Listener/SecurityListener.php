<?php

namespace Etu\Core\ApiBundle\Listener;

use Etu\Core\ApiBundle\Formatter\DataFormatter;
use Etu\Core\ApiBundle\Oauth\OauthServer;
use Doctrine\Common\Annotations\Reader;
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
     * @var DataFormatter
     */
    protected $formatter;

    /**
     * @param OauthServer $server
     * @param Reader $reader
     * @param DataFormatter $formatter
     */
    public function __construct(OAuthServer $server, Reader $reader, DataFormatter $formatter)
    {
        $this->server = $server;
        $this->reader = $reader;
        $this->formatter = $formatter;
    }

    /**
     * @param GetResponseEvent $event
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @return bool
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (strpos($event->getRequest()->attributes->get('_controller'), 'Api\\Resource') !== false) {
            header('Access-Control-Allow-Origin: *');

            $controller = explode('::', $event->getRequest()->attributes->get('_controller'));
            $reflection = new \ReflectionMethod($controller[0], $controller[1]);

            $scopeAnnotation = $this->reader->getMethodAnnotation($reflection, 'Etu\\Core\\ApiBundle\\Framework\\Annotation\\Scope');

            if ($scopeAnnotation) {
                $requiredScope = $scopeAnnotation->value;
            } else {
                $requiredScope = null;
            }

            if (! $requiredScope) {
                $requiredScope = 'public';
            }

            $request = $event->getRequest();

            $token = $request->query->get('access_token');

            $access = $this->server->checkAccess($token, $requiredScope);

            if (! $access->isGranted()) {
                $event->setResponse($this->formatter->format($event->getRequest(), [
                    'error' => $access->getError(),
                    'error_message' => $access->getErrorMessage(),
                ], 403));
            } else {
                $event->getRequest()->attributes->set('_oauth_token', $access->getToken());
            }
        }
    }
}
