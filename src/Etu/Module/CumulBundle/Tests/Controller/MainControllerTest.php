<?php

namespace Etu\Module\CumulBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;
use Etu\Core\UserBundle\Security\Authentication\OrgaToken;
use Etu\Core\UserBundle\Security\Authentication\UserToken;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
	public function testRestrictionIndex()
	{
		$client = static::createClient();

		$client->request('GET', '/cumul?q=user');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionIndexOrga()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new OrgaToken(MockUser::createOrga()));

		$client->request('GET', '/cumul?q=user');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	/**
	 * @todo
	 */
	public function testIndex()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));
		$client->followRedirects(true);

		$crawler = $client->request('GET', '/cumul', array('q' => 'user'));
	}
}