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
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionDaymailOrga()
    {
        $client = static::createClient();
        $client->getContainer()->get('security.token_storage')->setToken(new OrgaToken(MockUser::createOrga()));

        $client->request('GET', '/user/membership/orga/daymail');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionPreview()
    {
        $client = static::createClient();

        $client->request('GET', '/user/membership/orga/daymail/current/preview');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionPreviewOrga()
    {
        $client = static::createClient();
        $client->getContainer()->get('security.token_storage')->setToken(new OrgaToken(MockUser::createOrga()));

        $client->request('GET', '/user/membership/orga/daymail/current/preview');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testDaymail()
    {
        $client = static::createClient();
        $client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createUser()));

        $crawler = $client->request('GET', '/user/membership/orga/daymail');
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
    }

    public function testPreview()
    {
        $client = static::createClient();
        $client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createUser()));

        $client->request('GET', '/user/membership/orga/daymail/current/preview');

        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
    }
}