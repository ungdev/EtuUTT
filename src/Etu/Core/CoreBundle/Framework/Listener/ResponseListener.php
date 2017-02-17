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

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (mb_substr($request->get('_controller'), 0, 3) != 'Etu') {
            return;
        }

        if ($request->get('_route') == 'notifs_new') {
            return;
        }

        if ($response->headers->get('Content-Type') == 'application/json') {
            return;
        }

        if ($response->headers->get('Content-Type') == 'application/js') {
            return;
        }

        if ($response->headers->get('Content-Type') == 'application/xml') {
            return;
        }

        $this->session->set('etu.last_url', $request->getRequestUri());
    }
}
