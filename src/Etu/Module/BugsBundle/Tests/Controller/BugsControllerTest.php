<?php

namespace Etu\Module\BugsBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\EtuWebTestCase;

class BugsControllerTest extends EtuWebTestCase
{
    public function testRestrictionIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/bugs');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionClosed()
    {
        $client = static::createClient();

        $client->request('GET', '/bugs/closed');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionView()
    {
        $client = static::createClient();

        $client->request('GET', '/bugs/1-issue-title');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionCreate()
    {
        $client = static::createClient();

        $client->request('GET', '/bugs/create');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionEdit()
    {
        $client = static::createClient();

        $client->request('GET', '/bugs/1-issue-title/edit');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionEditComment()
    {
        $client = static::createClient();

        $client->request('GET', '/bugs/1-issue-title/edit/comment/1');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionEditCommentNotAuthor()
    {
        $client = $this->createUserClient();

        $client->request('GET', '/bugs/1-issue-title/edit/comment/1');
        $this->assertEquals($client->getResponse()->getStatusCode(), 403);
    }

    public function testIndex()
    {
        $client = $this->createUserClient();

        $crawler = $client->request('GET', '/bugs');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Bugs ouverts")')->count());
    }

    public function testClosed()
    {
        $client = $this->createUserClient();

        $crawler = $client->request('GET', '/bugs/closed');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Bugs résolus")')->count());
    }

    public function testView()
    {
        $client = $this->createUserClient();

        $crawler = $client->request('GET', '/bugs/1-issue-title');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Détails d\'un bug")')->count());
    }

    public function testCreate()
    {
        $client = $this->createUserClient();

        $crawler = $client->request('GET', '/bugs/create');

        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Signaler un bug")')->count());
    }

    public function testEdit()
    {
        $client = $this->createUserClient();

        $crawler = $client->request('GET', '/bugs/1-issue-title/edit');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier un bug")')->count());
    }
}
