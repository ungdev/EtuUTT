<?php

namespace Etu\Module\DaymailBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\EtuWebTestCase;

class MembershipsControllerTest extends EtuWebTestCase
{
    public function testRestrictionDaymail()
    {
        $client = static::createClient();

        $client->request('GET', '/user/membership/orga/daymail');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testRestrictionDaymailOrga()
    {
        $client = $this->createOrgaClient();

        $client->request('GET', '/user/membership/orga/daymail');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testRestrictionPreview()
    {
        $client = static::createClient();

        $client->request('GET', '/user/membership/orga/daymail/current/preview');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testRestrictionPreviewOrga()
    {
        $client = $this->createOrgaClient();

        $client->request('GET', '/user/membership/orga/daymail/current/preview');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testDaymail()
    {
        $client = $this->createUserClient();

        $crawler = $client->request('GET', '/user/membership/orga/daymail');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testPreview()
    {
        $client = $this->createUserClient();

        $client->request('GET', '/user/membership/orga/daymail/current/preview');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
