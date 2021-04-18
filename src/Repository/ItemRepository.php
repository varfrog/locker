<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Item;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class ItemRepository extends EntityRepository
{
    /**
     * @param User $user
     *
     * @return Item[]
     */
    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }
}
