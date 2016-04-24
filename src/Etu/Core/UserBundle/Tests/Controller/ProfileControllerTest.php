<?php

namespace Etu\Core\UserBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\EtuWebTestCase;

class ProfileControllerTest extends EtuWebTestCase
{
    public function testRestrictionProfile()
    {
        $client = static::createClient();

        $client->request('GET', '/user/profile');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionProfileEdit()
    {
        $client = static::createClient();

        $client->request('GET', '/user/profile/edit');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionProfileAvatar()
    {
        $client = static::createClient();

        $client->request('GET', '/user/profile/avatar');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionTrombiEdit()
    {
        $client = static::createClient();

        $client->request('GET', '/user/trombi/edit');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionView()
    {
        $client = static::createClient();

        $client->request('GET', '/user/admin');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testProfile()
    {
        $client = $this->createUserClient();
        $crawler = $client->request('GET', '/user/profile');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Mon profil")')->count());
    }

    public function testProfileEdit()
    {
        $client = $this->createUserClient();
        $crawler = $client->request('GET', '/user/profile/edit');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier mes informations")')->count(), $client->getResponse()->getContent());
    }

    public function testTrombiEdit()
    {
        $client = $this->createUserClient();
        $crawler = $client->request('GET', '/user/trombi/edit');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Mon trombinoscope")')->count());
    }

    public function testView()
    {
        $client = $this->createUserClient();
        $crawler = $client->request('GET', '/user/admin');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Détail d\'un profil")')->count());
    }
}
