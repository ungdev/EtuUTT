<?php

namespace Etu\Module\WikiBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;
use Etu\Core\UserBundle\Security\Authentication\UserToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @todo Finish the tests (not only restrictions tests)
 */
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
}