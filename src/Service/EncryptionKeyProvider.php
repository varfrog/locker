<?php

declare(strict_types=1);

namespace App\Service;

use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\EncryptionKey;

class EncryptionKeyProvider
{
    public function __construct(
        private string $encryptionKeyPath
    ) {
    }

    public function getKey(): EncryptionKey
    {
        // todo: load this in __constructor, keep as a HiddenString, to avoid loading every time
        return KeyFactory::loadEncryptionKey($this->encryptionKeyPath);
    }
}
