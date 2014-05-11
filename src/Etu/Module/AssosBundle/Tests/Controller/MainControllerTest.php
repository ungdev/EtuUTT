<?php

namespace Etu\Module\AssosBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;
use Etu\Core\UserBundle\Security\Authentication\UserToken;
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
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Détail d\'une association")')->count());
	}

    public function testRestrictMembers()
    {
        $client = static::createClient();

        $client->request('GET', '/orgas/orga/members');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testMembers()
    {
        $client = static::createClient();
        $client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

        $crawler = $client->request('GET', '/orgas/orga/members');

        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Membres d\'une assocation")')->count());
    }
}