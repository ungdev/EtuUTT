<?php

namespace Etu\Core\UserBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;

use Etu\Core\UserBundle\Security\Authentication\OrgaToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrgaControllerTest extends WebTestCase
{
	public function testRestrictionIndex()
	{
		$client = static::createClient();

		$client->request('GET', '/orga');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionAvatar()
	{
		$client = static::createClient();

		$client->request('GET', '/orga/avatar');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionMembers()
	{
		$client = static::createClient();

		$client->request('GET', '/orga/members');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testIndex()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new OrgaToken(MockUser::createOrga()));

		$crawler = $client->request('GET', '/orga');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Association")')->count());
	}

	public function testAvatar()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.token_storage')->setToken(new OrgaToken(MockUser::createOrga()));

		$crawler = $client->request('GET', '/orga/avatar');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier votre logo")')->count());
	}
}