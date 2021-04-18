<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Exception\CrypterException;
use ParagonIE\Halite\Alerts\HaliteAlert;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\HiddenString\HiddenString;
use SodiumException;

class Crypter
{
    public function __construct(
        private EncryptionKeyProvider $encryptionKeyProvider
    ) {
    }

    /**
     * @param HiddenString $dataInPlainText
     *
     * @return string
     *
     * @throws CrypterException
     */
    public function encrypt(HiddenString $dataInPlainText): string
    {
        try {
            return Crypto::encrypt($dataInPlainText, $this->encryptionKeyProvider->getKey());
        } catch (SodiumException | HaliteAlert $exception) {
            throw new CrypterException('Cannot encrypt', 0, $exception);
        }
    }

    /**
     * @param string $data
     * @param User $user
     *
     * @return string
     *
     * @throws CrypterException
     */
    public function decrypt(string $data): string
    {
        try {
            $hiddenString = Crypto::decrypt($data, $this->encryptionKeyProvider->getKey());
        } catch (SodiumException | HaliteAlert $exception) {
            throw new CrypterException('Cannot decrypt', 0, $exception);
        }

        return $hiddenString->getString();
    }
}
