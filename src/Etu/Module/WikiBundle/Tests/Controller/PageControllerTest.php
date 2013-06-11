<?php

namespace Etu\Module\WikiBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;
use Etu\Core\UserBundle\Security\Authentication\UserToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PageControllerTest extends WebTestCase
{
	public function testRestrictionCreateAnonymous()
	{
		$client = static::createClient();

		$client->request('GET', '/wiki/orga/create');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionCreateUser()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/create');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionCreateCategoryAnonymous()
	{
		$client = static::createClient();

		$client->request('GET', '/wiki/orga/create-category');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionCreateCategoryUser()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/create-category');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionEditAnonymous()
	{
		$client = static::createClient();

		$client->request('GET', '/wiki/orga/2-page/edit');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionEditUser()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/2-page/edit');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionRevisionAnonymous()
	{
		$client = static::createClient();

		$client->request('GET', '/wiki/orga/2-page/revision/3');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionRevisionUser()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/2-page/revision/3');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionPermissionsAnonymous()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/2-page/permissions');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionPermissionsUser()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/2-page/permissions');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionRemoveAnonymous()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/2-page/delete');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionRemoveUser()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/2-page/delete');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionViewAnonymous()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/2-page');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionViewUser()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/2-page');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testCreate()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/wiki/orga/create');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Créer une page")')->count());
	}

	public function testCreateCategory()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/wiki/orga/create-category');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Créer une catégorie")')->count());
	}

	public function testEdit()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/wiki/orga/2-page/edit');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier une page")')->count());
	}

	public function testRevision()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/wiki/orga/2-page/revision/4');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Voir une révision")')->count());
	}

	public function testPermissions()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/wiki/orga/2-page/permissions');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier les permissions")')->count());
	}

	public function testRemove()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/wiki/orga/2-page/delete');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Supprimer une page")')->count());
	}

	public function testView()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/wiki/orga/2-page');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Lire une page")')->count());
	}
}