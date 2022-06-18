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
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);

        $authenticatedUser =    $this->createUserAndLogIn($client, 'test@test.pl', 'test');
        $otherUser = $this->createUser('otheruser@test.pl', 'testother');

        $cheesyData = [
            'title' => 'Mystery cheese... kinda green',
            'description' => 'What mysteries does it hold?',
            'price' => 5000
        ];

        $client->request(Request::METHOD_POST, '/api/cheeses', [
            'json' => $cheesyData,
        ]);
        $this->assertResponseStatusCodeSame(201);

        $client->request(Request::METHOD_POST, '/api/cheeses', [
            'json' => $cheesyData + ['owner' => '/api/users/' . $otherUser->getId()],
        ]);
        $this->assertResponseStatusCodeSame(422, 'not passing the correct owner');

        $client->request(Request::METHOD_POST, '/api/cheeses', [
            'json' => $cheesyData + ['owner' => '/api/users/' . $authenticatedUser->getId()],
        ]);
        $this->assertResponseStatusCodeSame(201);
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
