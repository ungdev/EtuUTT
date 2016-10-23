<?php

namespace Test\Etu\Core\UserBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\EtuWebTestCase;

class AuthControllerTest extends EtuWebTestCase
{
    public function testRestrictionConnect()
    {
        $client = $this->createUserClient();
        $client->request('GET', '/user');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302, $client->getResponse()->getContent());
    }

    public function testRestrictionConnectCAS()
    {
        $client = $this->createUserClient();
        $client->request('GET', '/user/cas');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302, $client->getResponse()->getContent());
    }

    public function testRestrictionConnectExternal()
    {
        $client = $this->createUserClient();
        $client->request('GET', '/user/external');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302, $client->getResponse()->getContent());
    }

    public function testRestrictionDisconnect()
    {
        $client = static::createClient();

        $client->request('GET', '/user/disconnect');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302, $client->getResponse()->getContent());
    }

    public function testConnect()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/user');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Connexion")')->count(), $client->getResponse()->getContent());
    }

    public function testConnectExternal()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/user/external');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Connexion d\'un exterieur")')->count(), $client->getResponse()->getContent());
    }
}
