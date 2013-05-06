<?php

namespace Etu\Core\UserBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;
use Etu\Core\UserBundle\Security\Authentication\UserToken;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
	public function testRestrictionConnect()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/user');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionConnectCAS()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/user/cas');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionConnectExternal()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$client->request('GET', '/user/external');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionDisconnect()
	{
		$client = static::createClient();

		$client->request('GET', '/user/disconnect');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testConnect()
	{
		$client = static::createClient();

		$crawler = $client->request('GET', '/user');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Connexion")')->count());
	}

	public function testConnectExternal()
	{
		$client = static::createClient();

		$crawler = $client->request('GET', '/user/external');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Connexion d\'un exterieur")')->count());
	}
}