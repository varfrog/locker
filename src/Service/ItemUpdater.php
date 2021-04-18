<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Item;
use ParagonIE\HiddenString\HiddenString;

class ItemUpdater
{
    public function __construct(private Crypter $crypter)
    {
    }

    public function updateItem(Item $item, HiddenString $newDataInPlainText): Item
    {
        return $item->setData($this->crypter->encrypt($newDataInPlainText));
    }
}
