<?php

namespace Etu\Core\UserBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;
use Etu\Core\UserBundle\Security\Authentication\UserToken;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
	public function testRestrictionUsers()
	{
		$client = static::createClient();

		$client->request('GET', '/admin/users');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionUserCreate()
	{
		$client = static::createClient();

		$client->request('GET', '/admin/user/create');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionUserEdit()
	{
		$client = static::createClient();

		$client->request('GET', '/admin/user/galopint/edit');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionUserPermissions()
	{
		$client = static::createClient();

		$client->request('GET', '/admin/user/galopint/permissions');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionUserAvatar()
	{
		$client = static::createClient();

		$client->request('GET', '/admin/user/galopint/avatar');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionUserReadOnly()
	{
		$client = static::createClient();

		$client->request('GET', '/admin/user/galopint/toggle-readonly');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionUserDelete()
	{
		$client = static::createClient();

		$client->request('GET', '/admin/user/galopint/delete');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

}