<?php

namespace Etu\Core\UserBundle\Security\Listener;

use Etu\Core\UserBundle\Security\Authentication\AnonymousToken;
use Etu\Core\UserBundle\Security\Authentication\AnonymousUser;
use Etu\Core\UserBundle\Security\Authentication\UserToken;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Listener to connect CAS and Symfony.
 *
 * @author Titouan
 */
class KernelListener
{
	/**
	 * @var SecurityContext
	 */
	protected $securityContext;

	/**
	 * @var Session
	 */
	protected $session;

	/**
	 * Constructor
	 *
	 * @param SecurityContext $securityContext
	 * @param Session         $session
	 */
	public function __construct(SecurityContext $securityContext, Session $session)
	{
		$this->securityContext = $securityContext;
		$this->session = $session;
	}

	/**
	 * Called on kernel.request event. Find current user.
	 */
	public function onKernelRequest()
	{
		if ($this->session->get('user') instanceof UserInterface) {
			$this->securityContext->setToken(new UserToken($this->session->get('user')));
		} else {
			$this->securityContext->setToken(new AnonymousToken());
		}
	}
}