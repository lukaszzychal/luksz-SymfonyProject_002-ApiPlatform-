<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Test\CustomApiTestCase;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class CheeseListingResourceTest extends CustomApiTestCase
{

    use ReloadDatabaseTrait;

    public function testSomething(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_POST, '/api/cheeses', [
            'headers' => [
                'Content-Type' => 'application/json',
                'json' => []
            ]
        ]);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);

        $this->createUserAndLogIn($client, 'cheeseplease@example.com', 'foo');

        $client->request('POST', '/api/cheeses', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                // 'title' => 'test'
            ],
        ]);
        $this->assertResponseStatusCodeSame(400);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);

        // $response = static::createClient()->request('GET', '/');

        // $this->assertResponseIsSuccessful();
        // $this->assertJsonContains(['@id' => '/']);
    }
}
