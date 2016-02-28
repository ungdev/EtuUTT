<?php

namespace Etu\Module\DaymailBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;
use Etu\Core\UserBundle\Security\Authentication\OrgaToken;
use Etu\Core\UserBundle\Security\Authentication\UserToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MembershipsControllerTest extends WebTestCase
{
    public function testRestrictionDaymail()
    {
        $client = static::createClient();

        $client->request('GET', '/user/membership/orga/daymail');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testRestrictionDaymailOrga()
    {
        $client = static::createClient();
        $client->getContainer()->get('security.token_storage')->setToken(new OrgaToken(MockUser::createOrga()));

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
        $client = static::createClient();
        $client->getContainer()->get('security.token_storage')->setToken(new OrgaToken(MockUser::createOrga()));

        $client->request('GET', '/user/membership/orga/daymail/current/preview');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testDaymail()
    {
        $client = static::createClient();
        $client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createUser()));

        $crawler = $client->request('GET', '/user/membership/orga/daymail');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testPreview()
    {
        $client = static::createClient();
        $client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createUser()));

        $client->request('GET', '/user/membership/orga/daymail/current/preview');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}