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
        return KeyFactory::loadEncryptionKey($this->encryptionKeyPath);
    }
}
