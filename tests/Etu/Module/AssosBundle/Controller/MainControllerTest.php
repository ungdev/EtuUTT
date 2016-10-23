<?php

namespace Test\Etu\Module\AssosBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\EtuWebTestCase;

class MainControllerTest extends EtuWebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/orgas');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Les associations")')->count());
    }

    public function testView()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/orgas/orga');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Détail d\'une association")')->count());
    }

    public function testRestrictMembers()
    {
        $client = static::createClient();

        $client->request('GET', '/orgas/orga/members');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testMembers()
    {
        $client = $this->createUserClient();
        $crawler = $client->request('GET', '/orgas/orga/members');

        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Membres d\'une assocation")')->count());
    }
}
