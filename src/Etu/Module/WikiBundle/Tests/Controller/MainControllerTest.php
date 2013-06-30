<?php

namespace Etu\Module\WikiBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;
use Etu\Core\UserBundle\Security\Authentication\OrgaToken;
use Etu\Core\UserBundle\Security\Authentication\UserToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
	public function testRestrictionIndex()
	{
		$client = static::createClient();

		$client->request('GET', '/wiki');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionEditAnonymous()
	{
		$client = static::createClient();

		$client->request('GET', '/wiki/home/edit');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionEditUser()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/home/edit');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionEditOrga()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new OrgaToken(MockUser::createOrga()));

		$client->request('GET', '/wiki/home/edit');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionRevisionAnonymous()
	{
		$client = static::createClient();

		$client->request('GET', '/wiki/home/revision/1');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionRevisionUser()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/wiki/home/revision/1');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionRevisionOrga()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new OrgaToken(MockUser::createOrga()));

		$client->request('GET', '/wiki/home/revision/1');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testIndex()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$crawler = $client->request('GET', '/wiki');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Wiki des associations")')->count());
	}

	public function testIndexOrga()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new OrgaToken(MockUser::createOrga()));

		$crawler = $client->request('GET', '/wiki');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Wiki des associations")')->count());
	}

	public function testEdit()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/wiki/home/edit');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier l\'accueil")')->count());
	}

	public function testRevision()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/wiki/home/revision/1');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Voir une révision")')->count());
	}
}