<?php

namespace Etu\Module\AssosBundle\Test\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
	public function testIndex()
	{
		$client = static::createClient();

		$crawler = $client->request('GET', '/orgas');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Les associations")')->count());
	}

	public function testView()
	{
		$client = static::createClient();

		$crawler = $client->request('GET', '/orgas/orga');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Détail d\'une assocation")')->count());
	}

	public function testMembers()
	{
		$client = static::createClient();

		$crawler = $client->request('GET', '/orgas/orga/members');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Membres d\'une assocation")')->count());
	}
}