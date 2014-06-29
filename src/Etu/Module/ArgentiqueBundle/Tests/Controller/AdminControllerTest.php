<?php

namespace Etu\Module\ArgentiqueBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;
use Etu\Core\UserBundle\Security\Authentication\UserToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    public function testRestrictSynchronizeAnonymous()
    {
        $client = static::createClient();

        $client->request('GET', '/argentique/admin/synchronize');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictSynchronizeUnauthorized()
    {
        $client = static::createClient();
        $client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

        $client->request('GET', '/argentique/admin/synchronize');
        $this->assertEquals($client->getResponse()->getStatusCode(), 403);
    }

    public function testRestrictSynchronizeStartAnonymous()
    {
        $client = static::createClient();

        $client->request('GET', '/argentique/admin/synchronize/start');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictSynchronizeStartUnauthorized()
    {
        $client = static::createClient();
        $client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

        $client->request('GET', '/argentique/admin/synchronize/start');
        $this->assertEquals($client->getResponse()->getStatusCode(), 403);
    }

    public function testRestrictSynchronizeEndAnonymous()
    {
        $client = static::createClient();

        $client->request('GET', '/argentique/admin/synchronize/end');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictSynchronizeEndUnauthorized()
    {
        $client = static::createClient();
        $client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

        $client->request('GET', '/argentique/admin/synchronize/end');
        $this->assertEquals($client->getResponse()->getStatusCode(), 403);
    }

    public function testRestrictSynchronizePhotoAnonymous()
    {
        $client = static::createClient();

        $client->request('GET', '/argentique/admin/synchronize/43');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictSynchronizePhotoUnauthorized()
    {
        $client = static::createClient();
        $client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

        $client->request('GET', '/argentique/admin/synchronize/43');
        $this->assertEquals($client->getResponse()->getStatusCode(), 403);
    }
}