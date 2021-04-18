<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Item;
use App\Entity\User;
use App\Service\Crypter;
use App\Service\ItemFactory;
use Doctrine\Persistence\ObjectManager;
use ParagonIE\HiddenString\HiddenString;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ItemFactoryTest extends TestCase
{
    private ObjectManager|MockObject $objectManager;
    private Crypter|MockObject $crypter;
    private ItemFactory $itemFactory;

    public function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->crypter = $this->createMock(Crypter::class);
        $this->itemFactory = new ItemFactory($this->objectManager, $this->crypter);
    }

    public function testCreatePersists(): void
    {
        /** @var User */
        $user = $this->createMock(User::class);

        $this->objectManager
            ->expects($this->atLeastOnce())
            ->method('persist')
        ;

        $this->itemFactory->create($user, new HiddenString('secret data'));
    }

    public function testCreateEncryptsData(): void
    {
        /** @var User */
        $user = $this->createMock(User::class);

        $this->crypter
            ->method('encrypt')
            ->willReturn('encrypted text')
        ;


        $result = $this->itemFactory->create($user, new HiddenString('secret data'));

        $this->assertSame('encrypted text', $result->getData());
        $this->assertSame($user, $result->getUser());
    }
}
