<?php

namespace Etu\Core\CoreBundle\Framework\Listener;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class LocaleListener
{
	/**
	 * @var Session
	 */
	protected $session;

	/**
	 * @var Translator
	 */
	protected $translator;

	/**
	 * @param Session    $session
	 * @param Translator $translator
	 */
	public function __construct(Session $session, Translator $translator)
	{
		$this->session = $session;
		$this->translator = $translator;
	}

	/**
	 * @param GetResponseEvent $event
	 */
	public function onKernelRequest(GetResponseEvent $event)
	{
		if ($this->session->has('_locale')) {
			$event->getRequest()->setLocale($this->session->get('_locale'));
			$this->translator->setLocale($this->session->get('_locale'));
		}
	}
}
