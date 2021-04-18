<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Item;

class DecryptingItemSerializer
{
    public function __construct(private Crypter $crypter)
    {
    }

    public function serialize(Item $item): array
    {
        return [
            'id' => $item->getId(), // we should not expose the auto-increment id to avoid guessing by iteration
            'data' => $this->crypter->decrypt($item->getData()),
            'created_at' => $item->getCreatedAt(),
            'updated_at' => $item->getUpdatedAt(),
        ];
    }
}
