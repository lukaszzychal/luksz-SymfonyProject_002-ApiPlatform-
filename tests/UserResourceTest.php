<?php

use App\Entity\User;
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
                'username' => 'newUserName',
                'roles' => ['ROLE_ADMIN'] // will be ignored
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'username' => 'newUserName'
        ]);
        $em = $this->getEntityManager();
        /** @var User $user */
        $user = $em->getRepository(User::class)->find($user->getId());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testGetUser()
    {
        $client = self::createClient();
        $user = $this->createUserAndLogIn($client, 'test@test.pl', 'test');
        $user->setPhonenumber('00-12-34-567');
        $em = $this->getEntityManager();
        $em->flush();

        $client->request(Request::METHOD_GET, '/api/users/' . $user->getId());
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'username' => 'test'
        ]);
        $responseData = $client->getResponse()->toArray();
        $this->assertArrayNotHasKey('phonenumber', $responseData);
        $em = $this->getEntityManager();
        /** @var User $user */
        $user = $em->getRepository(User::class)->find($user->getId());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());

        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->find($user->getId());
        $user->setRoles(['ROLE_ADMIN']);
        $em->flush();

        $em = $this->getEntityManager();
        /** @var User $user */
        $user = $em->getRepository(User::class)->find($user->getId());
        $this->assertEquals(['ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());

        $this->logIn($client, 'test@test.pl', 'test');

        $client->request(Request::METHOD_GET, '/api/users/' . $user->getId());
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'username' => 'test'
        ]);

        // dump($client->getResponse());
        $em = $this->getEntityManager();
        /** @var User $user */
        $user = $em->getRepository(User::class)->find($user->getId());
        $this->assertEquals(['ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());

        // $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'phonenumber' => '00-12-34-567'
        ]);
    }
}
