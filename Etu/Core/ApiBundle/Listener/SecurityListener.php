<?php

namespace Etu\Core\ApiBundle\Listener;

use Doctrine\Common\Annotations\Reader;
use Etu\Core\ApiBundle\Formatter\DataFormatter;
use Etu\Core\ApiBundle\Oauth\OauthServer;
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

    public function __construct(OAuthServer $server, Reader $reader, DataFormatter $formatter)
    {
        $this->server = $server;
        $this->reader = $reader;
        $this->formatter = $formatter;
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     *
     * @return bool
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (false !== mb_strpos($event->getRequest()->attributes->get('_controller'), 'Api\\Resource')) {
            $controller = explode('::', $event->getRequest()->attributes->get('_controller'));
            $reflection = new \ReflectionMethod($controller[0], $controller[1]);

            $scopeAnnotation = $this->reader->getMethodAnnotation($reflection, 'Etu\\Core\\ApiBundle\\Framework\\Annotation\\Scope');

            if ($scopeAnnotation) {
                $requiredScope = $scopeAnnotation->value;
            } else {
                $requiredScope = null;
            }

            if (!$requiredScope) {
                $requiredScope = 'public';
            }
            if ('external' == $requiredScope) {
                return true;
            }
            $request = $event->getRequest();

            $token = $request->query->get('access_token');
            if ($request->headers->has('Authorization')) {
                $authorizationHeader = $request->headers->get('Authorization');
                preg_match('/Bearer (.*)/i', $authorizationHeader, $matches);
                if (!empty($matches[1])) {
                    $token = $matches[1];
                }
            }

            $access = $this->server->checkAccess($token, $requiredScope);

            if (!$access->isGranted()) {
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
