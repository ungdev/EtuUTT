<?php

namespace Etu\Core\CoreBundle\Framework\Listener;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ResponseListener
{
    /**
     * @var Session
     */
    protected $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if ('Etu' != mb_substr($request->get('_controller'), 0, 3)) {
            return;
        }

        if ('notifs_new' == $request->get('_route')) {
            return;
        }

        if ('application/json' == $response->headers->get('Content-Type')) {
            return;
        }

        if ('application/js' == $response->headers->get('Content-Type')) {
            return;
        }

        if ('application/xml' == $response->headers->get('Content-Type')) {
            return;
        }

        $this->session->set('etu.last_url', $request->getRequestUri());
    }
}
