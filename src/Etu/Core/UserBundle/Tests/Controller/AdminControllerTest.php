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

	public function testUsers()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/admin/users');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Utilisateurs")')->count());
	}

	public function testUserCreate()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/admin/user/create');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Créer un utilisateur")')->count());
	}

	public function testUserEdit()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/admin/user/admin/edit');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier un utilisateur")')->count());
	}

	public function testUserAvatar()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/admin/user/admin/avatar');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier la photo")')->count());
	}

	public function testUserDelete()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/admin/user/admin/delete');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Supprimer un utilisateur")')->count());
	}

	public function testUserPermissions()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/admin/user/admin/permissions');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier les permissions")')->count());
	}
}