<?php

namespace Etu\Core\CoreBundle\Framework\Listener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class TerminateListener
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
	 * @param PostResponseEvent $event
	 */
	public function onKernelTerminate(PostResponseEvent $event)
	{
		$this->session->set('etu.last_url', $event->getRequest()->getRequestUri());
	}
}
