<?php

namespace Etu\Module\CumulBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\EtuWebTestCase;

class MainControllerTest extends EtuWebTestCase
{
	public function testRestrictionIndex()
	{
		$client = static::createClient();

		$client->request('GET', '/cumul?q=user');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}

	public function testRestrictionIndexOrga()
	{
		$client = $this->createOrgaClient();

		$client->request('GET', '/cumul?q=user');
		$this->assertEquals($client->getResponse()->getStatusCode(), 302);
	}
}
