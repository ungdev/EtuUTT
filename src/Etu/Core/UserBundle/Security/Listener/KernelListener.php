<?php

namespace Etu\Core\UserBundle\Security\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Security\Authentication\AnonymousToken;
use Etu\Core\UserBundle\Security\Authentication\OrgaToken;
use Etu\Core\UserBundle\Security\Authentication\UserToken;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContext;

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
	 * @var Registry
	 */
	protected $doctrine;

	/**
	 * Constructor
	 *
	 * @param SecurityContext $securityContext
	 * @param Session         $session
	 * @param Registry        $doctrine
	 */
	public function __construct(SecurityContext $securityContext, Session $session, Registry $doctrine)
	{
		$this->securityContext = $securityContext;
		$this->session = $session;
		$this->doctrine = $doctrine;
	}

	/**
	 * Called on kernel.request event. Find current user.
	 */
	public function onKernelRequest(GetResponseEvent $event)
	{
		// User already found ? (Used by tests)
		if ($this->securityContext->getToken() !== null) {
			return;
		}

        // Cookie not found
        if ($event->getRequest()->cookies->has(md5('etuutt-session-cookie-name'))) {
            $cookie = $event->getRequest()->cookies->get(md5('etuutt-session-cookie-name'));

            /** @var EntityManager $em */
            $em = $this->doctrine->getManager();

            // Find session
            /** @var \Etu\Core\UserBundle\Entity\Session $session */
            $session = $em->getRepository('EtuUserBundle:Session')->findOneBy([ 'token' => $cookie ]);

            if ($session && $session->getExpireAt() > new \DateTime()) {
                if ($session->getEntityType() == \Etu\Core\UserBundle\Entity\Session::TYPE_ORGA) {
                    $this->session->set('user', null);
                    $this->session->set('user_data', null);

                    $orga = $em->getRepository('EtuUserBundle:Organization')->find($session->getEntityId());
                    $this->session->set('orga', $orga);

                    $this->securityContext->setToken(new OrgaToken($orga));

                    return;
                } elseif ($session->getEntityType() == \Etu\Core\UserBundle\Entity\Session::TYPE_USER) {
                    $this->session->set('orga', null);

                    $user = $em->getRepository('EtuUserBundle:User')->find($session->getEntityId());
                    $this->session->set('user', $user->getId());
                    $this->session->set('user_data', $user);

                    $this->securityContext->setToken(new UserToken($user));

                    return;
                }
            }
        }

        $this->session->set('user', null);
        $this->session->set('orga', null);

        $this->securityContext->setToken(new AnonymousToken());
	}
}