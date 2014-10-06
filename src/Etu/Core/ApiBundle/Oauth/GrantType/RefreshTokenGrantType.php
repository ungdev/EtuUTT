<?php

namespace Etu\Core\ApiBundle\Oauth\GrantType;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Entity\OauthAccessToken;
use Etu\Core\ApiBundle\Entity\OauthAuthorizationCode;
use Etu\Core\ApiBundle\Entity\OauthClient;
use Etu\Core\ApiBundle\Entity\OauthRefreshToken;
use Symfony\Component\HttpFoundation\Request;

class RefreshTokenGrantType implements GrantTypeInterface
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

        /** @var OauthRefreshToken $refreshToken */
        $refreshToken = $this->manager->getRepository('EtuCoreApiBundle:OauthRefreshToken')
            ->findOneBy([ 'token' => $request->request->get('refresh_token') ]);

        if (! $refreshToken) {
            throw new \RuntimeException('Refresh token code not found');
        }

        if ($refreshToken->getExpireAt() <= new \DateTime()) {
            throw new \RuntimeException('Refresh token code expired');
        }

        $token = new OauthAccessToken();
        $token->setClient($client);
        $token->setUser($refreshToken->getUser());
        $token->generateToken();

        foreach ($refreshToken->getScopes() as $scope) {
            $token->addScope($scope);
        }

        $token->setRefreshToken($refreshToken);

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
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'refresh_token';
    }
}