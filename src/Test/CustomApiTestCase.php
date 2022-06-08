<?php

namespace App\Test;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\CheeseListing;
// use App\ApiPlatform\Test\Client;
use App\Entity\User;
use App\Repository\CheeseListingRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CustomApiTestCase extends ApiTestCase
{



    // '$argon2id$v=19$m=65536,t=6,p=1$AIC3IESQ64NgHfpVQZqviw$1c7M56xyiaQFBjlUBc7T0s53/PzZCjV56lbHnhOUXx8');

    protected function createUser(string $email, string $password): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername(substr($email, 0, strpos($email, '@')));
        // $user->setRoles(array_merge($user->getRoles(), ['ROLE_ADMIN']));
        // $user->setRoles(['ROLE_ADMIN']);



        $encodePassword = self::getContainer()->get('security.password_encoder')->encodePassword($user, $password);

        $user->setPassword($encodePassword);



        /**
         * @var Registry
         */
        $doctrine = $this->getDoctrine();


        /**
         * @var EntityManagerInterface $em
         */
        $em = $this->getEntityManager();

        $em->persist($user);
        $em->flush();

        /**
         * @var UserRepository
         */
        $repo = self::getContainer()->get(UserRepository::class);
        /**
         * @var User
         */
        $userFromDB = $repo->findOneById($user->getId());

        // dd($userFromDB->getRoles());


        return $user;
    }

    protected function logIn(Client $client, string $email, string $password): void
    {

        $client->request(
            Request::METHOD_POST,
            '/login',
            [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'email' => $email,
                    'password' => $password
                ]
            ]
        );

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NO_CONTENT);
    }


    protected function createUserAndLogIn(Client $client, string $email, string $password): User
    {
        $user = $this->createUser($email, $password);
        $this->logIn($client, $email, $password);
        return $user;
    }

    protected function getDoctrine(): Registry
    {
        return  self::getContainer()->get('doctrine');
    }

    protected function getEntityManager(): EntityManagerInterface
    {

        return $this->getDoctrine()->getManager();
    }


    protected function createCheeseListing(User $user): CheeseListing
    {
        $cheeseListing = new CheeseListing('Block of cheddar');
        $cheeseListing->setOwner($user);
        $cheeseListing->setPrice(1000);
        $cheeseListing->setDescription('mmmm');

        $em = $this->getEntityManager();
        $em->persist($cheeseListing);
        $em->flush();

        return $cheeseListing;
    }
}
