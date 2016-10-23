<?php

namespace Etu\Core\UserBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\EtuWebTestCase;

class OrgaControllerTest extends EtuWebTestCase
{
    public function testRestrictionIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/orga');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionAvatar()
    {
        $client = static::createClient();

        $client->request('GET', '/orga/avatar');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionMembers()
    {
        $client = static::createClient();

        $client->request('GET', '/orga/members');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testIndex()
    {
        $client = $this->createOrgaClient();
        $crawler = $client->request('GET', '/orga');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Association")')->count());
    }

    public function testAvatar()
    {
        $client = $this->createOrgaClient();
        $crawler = $client->request('GET', '/orga/avatar');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier votre logo")')->count());
    }
}
