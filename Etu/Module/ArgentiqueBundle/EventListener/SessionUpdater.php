<?php

namespace Etu\Module\ArgentiqueBundle\EventListener;

use Firebase\JWT\JWT;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class SessionUpdater
{
    /**
     * @var AuthorizationChecker
     */
    protected $authorizationChecker;

    /**
     * @var Container
     */
    protected $container;

    public function __construct(AuthorizationChecker $authorizationChecker, ContainerInterface $container)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->container = $container;
    }

    /**
     * This function will update the php session that
     * allow images to be downloaded without starting the full symfony stack.
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $key = $this->container->getParameter('argentique.jwt.key');
        $algo = $this->container->getParameter('argentique.jwt.algo');

        $jwt = JWT::encode([
            'ROLE_ARGENTIQUE_READ' => $this->authorizationChecker->isGranted('ROLE_ARGENTIQUE_READ'),
        ], $key, $algo);

        $cookie = new Cookie('external_jwt', $jwt, strtotime('now + 10 minutes'), '/', null, false, true);
        $event->getResponse()->headers->setCookie($cookie);
    }
}
