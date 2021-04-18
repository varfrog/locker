<?php

declare(strict_types=1);

namespace App\Service;

use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\EncryptionKey;

class EncryptionKeyProvider
{
    private EncryptionKey $encryptionKey;

    public function __construct(string $encryptionKeyPath)
    {
        $this->encryptionKey = KeyFactory::loadEncryptionKey($encryptionKeyPath);
    }

    public function getKey(): EncryptionKey
    {
        return $this->encryptionKey;
    }
}
