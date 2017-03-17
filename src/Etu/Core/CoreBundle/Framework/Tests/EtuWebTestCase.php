<?php

namespace Etu\Core\CoreBundle\Framework\Tests;

use Etu\Core\UserBundle\Security\Authentication\Token\CasToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

class EtuWebTestCase extends WebTestCase
{
    protected function createAdminClient()
    {
        $client = static::createClient();
        $session = $client->getContainer()->get('session');

        $firewall = 'default';
        $user = MockUser::createAdminUser();
        $token = new CasToken($user, $user->getRoles());
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        return $client;
    }

    protected function createUserClient()
    {
        $client = static::createClient();
        $session = $client->getContainer()->get('session');

        $firewall = 'default';
        $user = MockUser::createUser();
        $token = new CasToken($user, $user->getRoles());
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        return $client;
    }

    protected function createOrgaClient()
    {
        $client = static::createClient();
        $session = $client->getContainer()->get('session');

        $firewall = 'default';
        $user = MockUser::createOrga();
        $token = new CasToken($user, $user->getRoles());
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        return $client;
    }
}
