<?php

namespace Test\Etu\Core\UserBundle\Test\Controller;

use Etu\Core\CoreBundle\Framework\Tests\EtuWebTestCase;

class AdminControllerTest extends EtuWebTestCase
{
    public function testRestrictionUsers()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/users');
        $this->assertSame($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionUserCreate()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/user/create');
        $this->assertSame($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionUserEdit()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/user/galopint/edit');
        $this->assertSame($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionUserPermissions()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/user/galopint/permissions');
        $this->assertSame($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionUserAvatar()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/user/galopint/avatar');
        $this->assertSame($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionUserReadOnly()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/user/galopint/toggle-readonly');
        $this->assertSame($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionUserDelete()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/user/galopint/delete');
        $this->assertSame($client->getResponse()->getStatusCode(), 302);
    }

    public function testUsers()
    {
        $client = $this->createAdminClient();
        $crawler = $client->request('GET', '/admin/users');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Utilisateurs")')->count());
    }

    public function testUserCreate()
    {
        $client = $this->createAdminClient();
        $crawler = $client->request('GET', '/admin/user/create');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Créer un utilisateur")')->count());
    }

    public function testUserEdit()
    {
        $client = $this->createAdminClient();
        $crawler = $client->request('GET', '/admin/user/admin/edit');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier un utilisateur")')->count());
    }

    public function testUserAvatar()
    {
        $client = $this->createAdminClient();
        $crawler = $client->request('GET', '/admin/user/admin/avatar');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier la photo")')->count());
    }

    public function testUserDelete()
    {
        $client = $this->createAdminClient();
        $crawler = $client->request('GET', '/admin/user/admin/delete');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Supprimer un utilisateur")')->count());
    }

    public function testUserPermissions()
    {
        $client = $this->createAdminClient();
        $crawler = $client->request('GET', '/admin/user/admin/permissions');
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Modifier les permissions")')->count());
    }
}
