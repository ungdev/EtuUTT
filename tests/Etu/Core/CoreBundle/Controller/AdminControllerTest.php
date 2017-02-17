<?php

namespace Test\Etu\Core\CoreBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\EtuWebTestCase;

class AdminControllerTest extends EtuWebTestCase
{
    public function testRestrictionDashboard()
    {
        $client = $this->createUserClient();
        $client->request('GET', '/admin');
        $this->assertSame($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionModules()
    {
        $client = $this->createUserClient();
        $client->request('GET', '/admin/modules');
        $this->assertSame($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionPages()
    {
        $client = $this->createUserClient();
        $client->request('GET', '/admin/pages');
        $this->assertSame($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionPageCreate()
    {
        $client = $this->createUserClient();
        $client->request('GET', '/admin/page/create');
        $this->assertSame($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionPageEdit()
    {
        $client = $this->createUserClient();
        $client->request('GET', '/admin/page/edit/0');
        $this->assertSame($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionPageDelete()
    {
        $client = $this->createUserClient();
        $client->request('GET', '/admin/page/delete/0');
        $this->assertSame($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionPageDeleteConfirm()
    {
        $client = $this->createUserClient();
        $client->request('GET', '/admin/page/delete/0/confirm');
        $this->assertSame($client->getResponse()->getStatusCode(), 302);
    }

    public function testIndex()
    {
        $client = $this->createAdminClient();
        $crawler = $client->request('GET', '/admin');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Tableau de bord")')->count());
    }

    public function testModules()
    {
        $client = $this->createAdminClient();
        $crawler = $client->request('GET', '/admin/modules');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Modules")')->count());
    }

    public function testPages()
    {
        $client = $this->createAdminClient();
        $crawler = $client->request('GET', '/admin/pages');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Pages statiques")')->count());
    }

    public function testPageEdit()
    {
        $client = $this->createAdminClient();
        $crawler = $client->request('GET', '/admin/page/edit/1');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier une page")')->count());
    }

    public function testPageDelete()
    {
        $client = $this->createAdminClient();
        $crawler = $client->request('GET', '/admin/page/delete/1');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Supprimer une page")')->count());
    }

    public function testPageCreate()
    {
        $client = $this->createAdminClient();
        $crawler = $client->request('GET', '/admin/page/create');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Créer une page")')->count());
    }
}
