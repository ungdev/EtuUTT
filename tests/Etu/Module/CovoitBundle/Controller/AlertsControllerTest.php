<?php

namespace Test\Etu\Module\CovoitBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AlertsControllerTest extends WebTestCase
{
    public function testRestrictionIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/covoiturage/private/alerts');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionCreate()
    {
        $client = static::createClient();

        $client->request('GET', '/covoiturage/private/alerts/create');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testRestrictionEdit()
    {
        $client = static::createClient();

        $client->request('GET', '/covoiturage/private/1/edit');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    /*
    public function testRestrictionDelete()
    {
        $client = static::createClient();

        $client->request('GET', '/covoiturage/private/1/delete');
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }
    */
}
