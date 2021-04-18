<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TokenToUserResolver
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private UserRepository $userRepository
    ) {
    }

    public function resolveUser(): ?User
    {
        $tokenUser = $this->tokenStorage->getToken()?->getUser();
        if ($tokenUser === null) {
            return null;
        }

        return $this->userRepository->findOneByUsername($tokenUser->getUsername());
    }
}
