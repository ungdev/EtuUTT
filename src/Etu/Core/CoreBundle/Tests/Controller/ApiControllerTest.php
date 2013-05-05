<?php

namespace Etu\Core\CoreBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;
use Etu\Core\UserBundle\Security\Authentication\UserToken;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{
	public function testAnonymous()
	{
		$client = static::createClient();

		$client->request('GET', '/api/follow/issue/1');
		$this->assertEquals($client->getResponse()->getStatusCode(), 403);

		$client->request('GET', '/api/unfollow/issue/1');
		$this->assertEquals($client->getResponse()->getStatusCode(), 403);

		$client->request('GET', '/api/notifs/new');
		$this->assertEquals($client->getResponse()->getStatusCode(), 403);
	}

	public function testUserFollow()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/api/follow/issue/1');
		$this->assertEquals($client->getResponse()->getStatusCode(), 200);
	}

	public function testUserUnfollow()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/api/unfollow/issue/1');
		$this->assertEquals($client->getResponse()->getStatusCode(), 200);
	}

	public function testUserNewNotifs()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/api/notifs/new');
		$this->assertEquals($client->getResponse()->getStatusCode(), 200);
		$this->assertContains('result', $client->getResponse()->getContent());
	}
}