<?php

namespace Etu\Core\UserBundle\Security\Firewall;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class CasListener implements ListenerInterface
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event)
    {
    }

}
