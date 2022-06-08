<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\CheeseListing;
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

    public function testCreateCheeses(): void
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

    public function testUpdateCheeseListing()
    {
        $client = self::createClient();
        $user1 = $this->createUser('user1@test.pl', 'test');
        $user2 = $this->createUser('user2@test.pl', 'test');

        $cheeseListing = $this->createCheeseListing($user1);

        $this->logIn($client, 'user1@test.pl', 'test');
        $client->request(Request::METHOD_PUT, '/api/cheeses/' . $cheeseListing->getId(), [
            'json' => [
                'title' => "Update!"
            ]
        ]);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
        // dump($client->getResponse()->getContent());

        $this->logIn($client, 'user2@test.pl', 'test');
        $client->request(Request::METHOD_PUT, '/api/cheeses/' . $cheeseListing->getId(), [
            'json' => [
                'title' => "Update!", 'owner' => '/api/users/' . $user2->getId()
            ]
        ]);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_FORBIDDEN, 'Only author can updated');
        // var_dump($client->getResponse()->getContent(true));



        // $client->request()
    }
}
