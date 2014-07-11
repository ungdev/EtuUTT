<?php

namespace Etu\Core\ApiBundle\Manager;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Exception\ScopeNotFoundException;

class ClientManager
{
    private $em;

    function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Creates a new client
     *
     * @param string $identifier
     *
     * @param array $redirect_uris
     *
     * @param array $grant_type
     *
     * @param array $scopes
     *
     * @return Client
     */
    public function createClient($identifier, array $redirect_uris = array(), array $grant_types = array(), array $scopes = array())
    {
        $client = new \Etu\Core\ApiBundle\Entity\Client();
        $client->setClientId($identifier);
        $client->setClientSecret($this->generateSecret());
        $client->setRedirectUri($redirect_uris);
        $client->setGrantTypes($grant_types);

        // Verify scopes
        foreach ($scopes as $scope) {
            // Get Scope
            $scopeObject = $this->em->getRepository('EtuCoreApiBundle:Scope')->find($scope);
            if (!$scopeObject) throw new ScopeNotFoundException();
        }

        $client->setScopes($scopes);

        // Store Client
        $this->em->persist($client);
        $this->em->flush();

        return $client;
    }

    /**
     * Creates a secret for a client
     *
     * @return A secret
     */
    protected function generateSecret() {
        return base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }
}
