<?php

namespace Etu\Module\BugsBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;
use Etu\Core\UserBundle\Security\Authentication\UserToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BugsControllerTest extends WebTestCase
{
	public function testRestrictionIndex()
	{
		$client = static::createClient();

		$client->request('GET', '/bugs');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionClosed()
	{
		$client = static::createClient();

		$client->request('GET', '/bugs/closed');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionView()
	{
		$client = static::createClient();

		$client->request('GET', '/bugs/1-issue-title');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionCreate()
	{
		$client = static::createClient();

		$client->request('GET', '/bugs/create');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionCreateConnectedWithoutPermission()
	{
		$client = static::createClient();

		$user = MockUser::createUser();
		$user->addRemovedPermission('bugs.add');

		$client->getContainer()->get('security.token_storage')->setToken(new UserToken($user));

		$client->request('GET', '/bugs/create');
		$this->assertEquals($client->getResponse()->getStatusCode(), 403);
	}

	public function testRestrictionEdit()
	{
		$client = static::createClient();

		$client->request('GET', '/bugs/1-issue-title/edit');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionEditComment()
	{
		$client = static::createClient();

		$client->request('GET', '/bugs/1-issue-title/edit/comment/1');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionEditCommentNotAuthor()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/bugs/1-issue-title/edit/comment/1');
		$this->assertEquals($client->getResponse()->getStatusCode(), 403);
	}

	public function testIndex()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createUser()));

		$crawler = $client->request('GET', '/bugs');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Bugs ouverts")')->count());
	}

	public function testClosed()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createUser()));

		$crawler = $client->request('GET', '/bugs/closed');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Bugs résolus")')->count());
	}

	public function testView()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createUser()));

		$crawler = $client->request('GET', '/bugs/1-issue-title');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Détails d\'un bug")')->count());
	}

	public function testCreate()
	{
		$user = MockUser::createUser();

		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createUser()));

		$crawler = $client->request('GET', '/bugs/create');

		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Signaler un bug")')->count());
	}

	public function testEdit()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createUser()));

		$crawler = $client->request('GET', '/bugs/1-issue-title/edit');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier un bug")')->count());
	}
}