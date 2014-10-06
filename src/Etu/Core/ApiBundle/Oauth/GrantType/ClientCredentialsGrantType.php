<?php

namespace Etu\Core\ApiBundle\Oauth\GrantType;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Entity\OauthAccessToken;
use Etu\Core\ApiBundle\Entity\OauthClient;
use Symfony\Component\HttpFoundation\Request;

class ClientCredentialsGrantType implements GrantTypeInterface
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

        $token = new OauthAccessToken();
        $token->setClient($client);
        $token->setUser($client->getUser());
        $token->generateToken();

        foreach ($client->getScopes() as $scope) {
            $token->addScope($scope);
        }

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
        return 'client_credentials';
    }
}