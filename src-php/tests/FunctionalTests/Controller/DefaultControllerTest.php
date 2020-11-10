<?php

namespace App\Tests\FuctionalTests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testSomething()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        self::assertResponseIsSuccessful();
    }
}
