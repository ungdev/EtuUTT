<?php

namespace Etu\Module\TrombiBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;
use Etu\Core\UserBundle\Security\Authentication\UserToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    public function testRestrictionIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/trombi');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testIndex()
    {
        $client = static::createClient();
        $client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createUser()));

        $crawler = $client->request('GET', '/trombi');
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Trombinoscope")')->count());
    }
}