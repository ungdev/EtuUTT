<?php

namespace Etu\Core\ApiBundle\Oauth\GrantType;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Entity\OauthAccessToken;
use Etu\Core\ApiBundle\Entity\OauthAuthorizationCode;
use Etu\Core\ApiBundle\Entity\OauthClient;
use Etu\Core\ApiBundle\Entity\OauthRefreshToken;
use Symfony\Component\HttpFoundation\Request;

class AuthorizationCodeGrantType implements GrantTypeInterface
{
    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Request $request
     * @return OauthAccessToken
     */
    public function createToken(Request $request)
    {
        /** @var OauthClient $client */
        $client = $request->attributes->get('_oauth_client');

        if (! $client) {
            throw new \RuntimeException('Client not found');
        }

        /** @var OauthAuthorizationCode $authorizationCode */
        $authorizationCode = $this->manager->getRepository('EtuCoreApiBundle:OauthAuthorizationCode')
            ->findOneBy([ 'code' => $request->request->get('authorization_code') ]);

        if (! $authorizationCode) {
            throw new \RuntimeException('Authorization code not found');
        }

        if ($authorizationCode->getExpireAt() <= new \DateTime()) {
            throw new \RuntimeException('Authorization code expired');
        }

        $authorizationCode->setExpireAt(new \DateTime('-1 second'));

        $token = new OauthAccessToken();
        $token->setClient($client);
        $token->setUser($authorizationCode->getUser());
        $token->generateToken();

        $refreshToken = new OauthRefreshToken();
        $refreshToken->setUser($authorizationCode->getUser());
        $refreshToken->setClient($client);
        $refreshToken->generateToken();

        foreach ($authorizationCode->getScopes() as $scope) {
            $token->addScope($scope);
            $refreshToken->addScope($scope);
        }

        $token->setRefreshToken($refreshToken);

        $this->manager->persist($authorizationCode);
        $this->manager->persist($refreshToken);
        $this->manager->persist($token);
        $this->manager->flush();

        return $token;
    }

    /**
     * @param OauthAccessToken $token
     * @return array
     */
    public function format(OauthAccessToken $token)
    {
        $scopes = [];

        foreach ($token->getScopes() as $scope) {
            $scopes[] = $scope->getName();
        }

        return [
            'access_token' => $token->getToken(),
            'expires_at' => $token->getExpireAt()->format('U'),
            'scopes' => $scopes,
            'refresh_token' => $token->getRefreshToken()->getToken()
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'authorization_code';
    }
}