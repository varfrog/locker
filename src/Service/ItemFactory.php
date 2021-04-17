<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Item;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;

class ItemFactory
{
    public function __construct(private ObjectManager $objectManager)
    {
    }

    public function create(User $user, string $data): Item
    {
        $item = (new Item())
            ->setUser($user)
            ->setData($data)
        ;
        $this->objectManager->persist($item);

        return $item;
    }
}
