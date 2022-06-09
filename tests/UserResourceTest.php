<?php

use App\Test\CustomApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Request;

class UserResourceTest extends CustomApiTestCase
{

    use ReloadDatabaseTrait;


    public function testCreateUser()
    {
        $client = self::createClient();

        $email = 'test@example.com';
        $username = 'test';
        $password = '1qaz';

        $client->request(Request::METHOD_POST, '/api/users', [
            'json' => [
                'email' => $email,
                'username' => $username,
                'password' => $password
            ]
        ]);

        $this->logIn($client, $email, $password);
    }

    public function testUpdateUser()
    {
        $client = self::createClient();

        $user = $this->createUserAndLogIn($client, 'test@test.pl', 'test');


        $client->request(Request::METHOD_PUT, '/api/users/' . $user->getId(), [
            'json' => [
                'username' => 'newUserName'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'username' => 'newUserName'
        ]);
    }
}
