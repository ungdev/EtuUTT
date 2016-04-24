<?php

namespace Etu\Module\BugsBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\EtuWebTestCase;

class BugsAdminControllerTest extends EtuWebTestCase
{
	public function testRestrictionAssign()
	{
		$client = static::createClient();

		$client->request('GET', '/admin/bugs/1-issue-title/assign');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionUnassign()
	{
		$client = static::createClient();

		$client->request('GET', '/admin/bugs/1-issue-title/unassign');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionCriticality()
	{
		$client = static::createClient();

		$client->request('GET', '/admin/bugs/1-issue-title/criticality');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionClose()
	{
		$client = static::createClient();

		$client->request('GET', '/admin/bugs/1-issue-title/close');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionOpen()
	{
		$client = static::createClient();

		$client->request('GET', '/admin/bugs/1-issue-title/open');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionDelete()
	{
		$client = static::createClient();

		$client->request('GET', '/admin/bugs/1-issue-title/delete');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionDeleteConfirm()
	{
		$client = static::createClient();

		$client->request('GET', '/admin/bugs/1-issue-title/delete/confirm');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testDelete()
	{
		$client = $this->createAdminClient();
		
		$crawler = $client->request('GET', '/admin/bugs/1-issue-title/delete');
		$this->assertGreaterThan(0, $crawler->filter('h2:contains("Supprimer un bug")')->count());
	}
}
