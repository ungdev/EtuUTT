<?php

namespace Etu\Core\UserBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;
use Etu\Core\UserBundle\Security\Authentication\UserToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ScheduleControllerTest extends WebTestCase
{
	public function testRestrictionView()
	{
		$client = static::createClient();

		$client->request('GET', '/schedule');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionEdit()
	{
		$client = static::createClient();

		$client->request('GET', '/schedule/edit');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionPrint()
	{
		$client = static::createClient();

		$client->request('GET', '/schedule/print');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testView()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createUser()));

		$crawler = $client->request('GET', '/schedule');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Mon emploi du temps")')->count());
	}

	public function testEdit()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createUser()));

		$crawler = $client->request('GET', '/schedule/edit');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier")')->count());
	}

	public function testPrint()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new UserToken(MockUser::createUser()));

		$crawler = $client->request('GET', '/schedule/print');
		$this->assertGreaterThan(0, $crawler->filter('title:contains("Imprimer mon emploi du temps")')->count());
	}
}