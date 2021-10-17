<?php
namespace Etu\Core\UserBundle\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class LoginFormAuthenticator extends AbstractGuardAuthenticator
{

    private $router;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
    * Called on every request. Return whatever credentials you want to
    * be passed to getUser(). Returning null will cause this authenticator
    * to be skipped.
    */
    public function getCredentials(Request $request)
    {
        if('user_connect_external' === $request->attributes->get('_route')
            && $request->isMethod('POST')) {

            $request->getSession()->set(
                Security::LAST_USERNAME,
                $request->query->get("_username")
            );
            return [
                "username"=>$request->request->get("_username"),
                "password"=>$request->request->get("_password"),
                "csrf_token"=>$request->request->get("_csrf_token")
                ];
        }
        return NULL;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if(empty($credentials)) {
            return NULL;
        }

        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        // if a User object, checkCredentials() is called
        return $userProvider->loadUserByUsername($credentials["username"]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
    // on success, let the request continue
    return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new RedirectResponse($this->router->generate('user_connect_external'));
    }

    /**
    * Called when authentication is needed, but it's not sent
    */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->router->generate('user_connect_external'));
    }

    public function supportsRememberMe()
    {
        return false;
    }
}