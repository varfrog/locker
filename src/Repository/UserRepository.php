<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function findOneByUsername(string $username): ?User
    {
        /** @var User? $user */
        $user = $this->findOneBy(['username' => $username]);

        return $user;
    }
}
