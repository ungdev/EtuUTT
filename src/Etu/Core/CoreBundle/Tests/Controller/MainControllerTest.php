<?php

namespace Etu\Core\CoreBundle\Test\Controller;

use Etu\Core\UserBundle\Security\Authentication\AnonymousToken;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
	public function testIndexAnonymous()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new AnonymousToken());

		$crawler = $client->request('GET', '/');

		$this->assertGreaterThan(0, $crawler->filter('title:contains("Accueil")')->count());
	}

	public function testPages()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new AnonymousToken());

        $crawler = $client->request('GET', '/page/developpeurs');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Développeurs")')->count());
	}

	public function testChangeLocaleEnglish()
	{
		$client = static::createClient();
		$client->getContainer()->get('session')->set('_locale', 'en');
		$client->getContainer()->get('security.context')->setToken(new AnonymousToken());

		$client->followRedirects(true);

		$crawler = $client->request('GET', '/');

		$this->assertGreaterThan(0, $crawler->filter('title:contains("student website")')->count());
	}
}