<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFactory
{
    public function __construct(private UserPasswordEncoderInterface $userPasswordEncoder)
    {
    }

    public function createUser(string $username, string $password): User
    {
        $user = (new User())
            ->setUsername($username)
        ;
        $user->setPassword($this->userPasswordEncoder->encodePassword($user, $password));

        return $user;
    }
}
