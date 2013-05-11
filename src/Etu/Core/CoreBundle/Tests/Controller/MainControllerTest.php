<?php

namespace Etu\Core\CoreBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\MockUser;
use Etu\Core\UserBundle\Security\Authentication\AnonymousToken;
use Etu\Core\UserBundle\Security\Authentication\UserToken;

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

	public function testIndexUser()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createUser()));

		$crawler = $client->request('GET', '/');

		$this->assertGreaterThan(0, $crawler->filter('title:contains("Mon flux")')->count());
	}

	public function testIndexAdmin()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new UserToken(MockUser::createAdminUser()));

		$crawler = $client->request('GET', '/');

		$this->assertGreaterThan(0, $crawler->filter('title:contains("Mon flux")')->count());
		$this->assertGreaterThan(0, $crawler->filter('a:contains("Administration")')->count());
	}

	public function testPages()
	{
		$client = static::createClient();
		$client->getContainer()->get('security.context')->setToken(new AnonymousToken());

		$pages = array(
			'developpeurs' => 'Développeurs',
			'nous-aider' => 'Nous aider',
			'mentions-legales' => 'Mentions légales',
			'l-equipe' => 'L\'équipe',
		);

		foreach ($pages as $slug => $name) {
			$crawler = $client->request('GET', '/page/'.$slug);
			$this->assertGreaterThan(0, $crawler->filter('h2:contains("'.$name.'")')->count());
		}
	}

	public function testChangeLocaleFromFrench()
	{
		$client = static::createClient();
		$client->getContainer()->get('session')->set('_locale', 'fr');
		$client->getContainer()->get('security.context')->setToken(new AnonymousToken());

		$client->followRedirects(true);

		$crawler = $client->request('GET', '/');

		$this->assertGreaterThan(0, $crawler->filter('title:contains("Site étudiant de l\'UTT")')->count());

		$link = $crawler->selectLink('English')->link();
		$crawler = $client->click($link);

		$this->assertGreaterThan(0, $crawler->filter('title:contains("student website")')->count());
	}

	public function testChangeLocaleFromEnglish()
	{
		$client = static::createClient();
		$client->getContainer()->get('session')->set('_locale', 'en');
		$client->getContainer()->get('security.context')->setToken(new AnonymousToken());

		$client->followRedirects(true);

		$crawler = $client->request('GET', '/');

		$this->assertGreaterThan(0, $crawler->filter('title:contains("student website")')->count());

		$link = $crawler->selectLink('Français')->link();
		$crawler = $client->click($link);

		$this->assertGreaterThan(0, $crawler->filter('title:contains("Site étudiant de l\'UTT")')->count());
	}
}