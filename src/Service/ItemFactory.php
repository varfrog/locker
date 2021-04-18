<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Item;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use ParagonIE\HiddenString\HiddenString;

class ItemFactory
{
    public function __construct(
        private ObjectManager $objectManager,
        private Crypter $crypter
    ) {
    }

    public function create(User $user, HiddenString $dataInPlainText): Item
    {
        $item = (new Item())
            ->setUser($user)
            ->setData($this->crypter->encrypt($dataInPlainText))
        ;
        $this->objectManager->persist($item);

        return $item;
    }
}
