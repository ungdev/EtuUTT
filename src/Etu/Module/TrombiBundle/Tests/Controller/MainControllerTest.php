<?php

namespace Etu\Module\TrombiBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\EtuWebTestCase;

class MainControllerTest extends EtuWebTestCase
{
    public function testRestrictionIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/trombi');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testIndex()
    {
        $client = $this->createUserClient();

        $crawler = $client->request('GET', '/trombi');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Trombinoscope")')->count());
    }
}
