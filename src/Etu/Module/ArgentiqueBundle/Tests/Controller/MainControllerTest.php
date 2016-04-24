<?php

namespace Etu\Module\ArgentiqueBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\EtuWebTestCase;

class MainControllerTest extends EtuWebTestCase
{
    public function testRestrictIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/argentique');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testIndex()
    {
        $client = $this->createUserClient();

        $crawler = $client->request('GET', '/argentique');

        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Argentique")')->count());
    }
}
