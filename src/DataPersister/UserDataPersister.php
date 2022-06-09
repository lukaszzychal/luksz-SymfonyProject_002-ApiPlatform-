<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserDataPersister implements DataPersisterInterface
{

    private EntityManagerInterface $entityManager;
    private UserPasswordEncoderInterface $userPasswordEncoderInterface;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $userPasswordEncoderInterface)
    {
        $this->entityManager = $entityManager;
        $this->userPasswordEncoderInterface = $userPasswordEncoderInterface;
    }


    /**
     * Is the data supported by the persister?
     *
     * @param mixed $data
     */
    public function supports($data): bool
    {
        return $data instanceof User;
    }

    /**
     * Persists the data.
     *
     * @param User $data
     *
     * @return object|void Void will not be supported in API Platform 3, an object should always be returned
     */
    public function persist($data)
    {
        if ($data->getPlainPassword()) {
            $data->setPassword(
                $this->userPasswordEncoderInterface->encodePassword($data, $data->getPlainPassword())
            );


            $data->eraseCredentials();
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();
    }

    /**
     * Removes the data.
     *
     * @param User $data
     */
    public function remove($data)
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
}
