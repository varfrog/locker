<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Service\Crypter;
use App\Service\EncryptionKeyProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CrypterTest extends TestCase
{
    private Crypter $crypter;
    private EncryptionKeyProvider|MockObject $encryptionKeyProvider;

    public function setUp(): void
    {
        $this->encryptionKeyProvider = new EncryptionKeyProvider($this->getResourcePath('encryption.key'));
        $this->crypter = new Crypter($this->encryptionKeyProvider);
    }

    public function testTwoWayEncryptionWorks()
    {
        $text = 'Foo bar baz house';

        $encryptedString = $this->crypter->encrypt($text);
        $decryptedString = $this->crypter->decrypt($encryptedString);

        self::assertNotSame($text, $encryptedString);
        self::assertSame($text, $decryptedString);
    }

    public function testDifferentResultsForDifferentStrings()
    {
        self::assertNotSame($this->crypter->encrypt('foo'), $this->crypter->encrypt('bar'));
    }

    private function getResourcePath(string $filename): string
    {
        return join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', $filename]);
    }
}
