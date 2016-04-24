<?php

namespace Etu\Core\CoreBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\EtuWebTestCase;

class MainControllerTest extends EtuWebTestCase
{
    public function testIndexAnonymous()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertGreaterThan(0, $crawler->filter('title:contains("Accueil")')->count());
    }

    public function testPages()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/page/developpeurs');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Développeurs")')->count());
    }

    public function testChangeLocaleEnglish()
    {
        $client = static::createClient();
        $client->getContainer()->get('session')->set('_locale', 'en');

        $client->followRedirects(true);

        $crawler = $client->request('GET', '/');

        $this->assertGreaterThan(0, $crawler->filter('title:contains("student website")')->count());
    }
}
