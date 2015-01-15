<?php

namespace Etu\Module\ArgentiqueBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;
use Etu\Core\UserBundle\Security\Authentication\UserToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    public function testRestrictIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/argentique');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testIndex()
    {
        $client = static::createClient();
        $client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

        $crawler = $client->request('GET', '/argentique');

        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Argentique")')->count());
    }
}
