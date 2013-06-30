<?php

namespace Etu\Module\WikiBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;
use Etu\Core\UserBundle\Security\Authentication\OrgaToken;
use Etu\Core\UserBundle\Security\Authentication\UserToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrgaControllerTest extends WebTestCase
{
	public function testRestrictionIndex()
	{
		$client = static::createClient();

		$client->request('GET', '/wiki/orga');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionEditAnonymous()
	{
		$client = static::createClient();

		$client->request('GET', '/wiki/orga/edit');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionEditUser()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/edit');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionRevisionAnonymous()
	{
		$client = static::createClient();

		$client->request('GET', '/wiki/orga/revision/4');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionRevisionUser()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/revision/3');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionPermissionsAnonymous()
	{
		$client = static::createClient();

		$client->request('GET', '/wiki/orga/permissions');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionPermissionsUser()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/permissions');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionTreeAnonymous()
	{
		$client = static::createClient();

		$client->request('GET', '/wiki/orga/tree');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionTreeUser()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/tree');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionEditCategoryAnonymous()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/category/1/edit');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionEditCategoryUser()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/category/1/edit');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionEditCategoryOrga()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new OrgaToken(MockUser::createOrga()));

		$client->request('GET', '/wiki/orga/category/1/edit');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionRemoveCategoryAnonymous()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/category/1/remove');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionRemoveCategoryUser()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/orga/category/1/remove');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionRemoveCategoryOrga()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new OrgaToken(MockUser::createOrga()));

		$client->request('GET', '/wiki/orga/category/1/remove');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testIndex()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$crawler = $client->request('GET', '/wiki/orga');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Wiki de Orga ORGA")')->count());
	}

	public function testIndexOrga()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new OrgaToken(MockUser::createOrga()));

		$crawler = $client->request('GET', '/wiki/orga');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Wiki de Orga ORGA")')->count());
	}

	public function testEdit()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/wiki/orga/edit');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier l\'accueil")')->count());
	}

	public function testEditOrga()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new OrgaToken(MockUser::createOrga()));

		$crawler = $client->request('GET', '/wiki/orga/edit');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier l\'accueil")')->count());
	}

	public function testRevision()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/wiki/orga/revision/4');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Voir une révision")')->count());
	}

	public function testRevisionOrga()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new OrgaToken(MockUser::createOrga()));

		$crawler = $client->request('GET', '/wiki/orga/revision/4');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Voir une révision")')->count());
	}

	public function testPermissions()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/wiki/orga/permissions');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier les permissions")')->count());
	}

	public function testPermissionsOrga()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new OrgaToken(MockUser::createOrga()));

		$crawler = $client->request('GET', '/wiki/orga/permissions');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier les permissions")')->count());
	}

	public function testTree()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/wiki/orga/tree');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier l\'arborescence")')->count());
	}

	public function testTreeOrga()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new OrgaToken(MockUser::createOrga()));

		$crawler = $client->request('GET', '/wiki/orga/tree');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier l\'arborescence")')->count());
	}

	public function testEditCategory()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/wiki/orga/category/2/edit');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier une catégorie")')->count());
	}

	public function testRemoveCategory()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/wiki/orga/category/2/remove');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Supprimer une catégorie")')->count());
	}
}