<?php

namespace Etu\Core\UserBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Etu\Core\UserBundle\Security\Authentication\Token\CasToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Etu\Core\UserBundle\Exception\OrganizationNotAuthorizedException;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AuthController extends Controller
{
    /**
     * Connect the user or the organization automatically if possible,
     * ask for method to connect otherwise.
     *
     * @Route("/user", name="user_connect")
     * @Template()
     */
    public function connectAction()
    {
        // Redirect to home if user is already authenticated
        if ($this->isGranted('IS_AUTHENTICATED_FULLY') || $this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        // If we are here because we fail to fill the external login form
        if (!empty($this->get('security.authentication_utils')->getLastUsername())) {
            return $this->redirect($this->generateUrl('user_connect_external'));
        }

        // Save login target if we have the precedent page
        if ($this->get('session')->has('etu.last_url')) {
            $this->get('session')->set('etu.login_target', $this->get('session')->get('etu.last_url'));
        } else {
            $this->get('session')->set('etu.login_target', $this->generateUrl('homepage'));
        }

        // Try to connect user automatically
        if ($this->getKernel()->getEnvironment() != 'test') {
            $this->initializeCAS();
            if (\phpCAS::isAuthenticated()) {
                return $this->redirect($this->generateUrl('user_connect_cas'));
            }
        }

        // If we can't auto-connect, we ask for the method
        return [];
    }

    /**
     * Get the answer from the CAS here then save user as logged in.
     * But if visitor is not comming from cas, he will be redirect to cas
     * to log in.
     *
     * @Route("/user/cas", name="user_connect_cas")
     */
    public function connectCasAction()
    {
        // Redirect to home if user is already authenticated
        if ($this->isGranted('IS_AUTHENTICATED_FULLY') || $this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        // Force cas login
        $this->initializeCAS();
        \phpCAS::forceAuthentication();

        // Save login token
        try {
            if (\phpCAS::isAuthenticated()) {
                $login = \phpCAS::getUser();
                $authToken = $this->get('security.authentication.manager')->authenticate(new CasToken($login));
                $this->get('security.token_storage')->setToken($authToken);
            }
        } catch (OrganizationNotAuthorizedException $e) {
            // Organization found on the LDAP, but not authorized by an admin
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'error',
                'message' => 'user.auth.connect.orga_exists_ldap',
            ]);

            return $this->redirect($this->generateUrl('homepage'));
        } catch (AuthenticationException $e) {
            // If user is authorized by cas, we shouldn't have an exception
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'error',
                'message' => 'user.auth.connect.unknownError',
            ]);

            return $this->redirect($this->generateUrl('homepage'));
        }

        // Select final redirection
        $redirection = $this->redirect($this->generateUrl('homepage'));
        if ($this->get('session')->has('etu.login_target') && !empty($this->get('session')->get('etu.login_target'))) {
            $redirection = $this->redirect($this->get('session')->get('etu.login_target'));
            $this->get('session')->remove('etu.login_target');
        }

        return $redirection;
    }

    /**
     * Show a form to let external user login. If submitted,
     * authentication_utils will log in the user automatically.
     * Cannot be used to log organization or user without password.
     *
     * @Route("/user/external", name="user_connect_external")
     * @Template()
     */
    public function connectExternalAction()
    {
        // Redirect to home if user is already authenticated
        if ($this->isGranted('IS_AUTHENTICATED_FULLY') || $this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'error',
                'message' => 'user.auth.connect.error',
            ]);
        }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return [
            'last_username' => $lastUsername,
        ];
    }

    /**
     * Redirect user to the right logout way for him : CAS or external.
     *
     * @Route("/user/logout", name="user_logout")
     */
    public function logoutAction()
    {
        // Logout from CAS
        $this->initializeCAS();
        if (\phpCAS::isAuthenticated()) {
            \phpCAS::logoutWithRedirectService($this->generateUrl('user_logout', [], UrlGeneratorInterface::ABSOLUTE_URL));
        }

        // Logout from EtuUTT
        if ($this->isGranted('IS_AUTHENTICATED_FULLY') || $this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('user_logout_external'));
        }

        // Yeah ! We are logged out !
        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'user.auth.connect.loggedOut',
        ]);

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * Log out user from EtuUTT.
     *
     * Can also be used with CAS user, but they will not be logged out from
     * the CAS server.
     * This uri is set as logout route `app/config/security.yml`
     * All the logout process is done by security.authentication_utils
     *
     * @Route("/user/logout/external", name="user_logout_external")
     */
    public function logoutExternalAction()
    {
    }

    /**
     * Route triggered when an authenticated user try to access a forbidden page.
     *
     * @Route("/forbidden", name="forbidden")
     */
    public function forbidden()
    {
        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'error',
            'message' => 'user.denied',
        ]);

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * Initialize the CAS connection.
     */
    private function initializeCAS()
    {
        \phpCAS::client(
            $this->container->getParameter('etu.cas.version'),
            $this->container->getParameter('etu.cas.host'),
            $this->container->getParameter('etu.cas.port'),
            $this->container->getParameter('etu.cas.path'),
            $this->container->getParameter('etu.cas.change_session_id')
        );
        \phpCAS::setNoCasServerValidation();
    }
}
