<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Item;
use App\Entity\User;
use App\Service\Crypter;
use App\Service\ItemUpdater;
use ParagonIE\HiddenString\HiddenString;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemUpdaterTest extends TestCase
{
    private ItemUpdater $itemUpdater;
    private Crypter|MockObject $crypter;

    public function setUp(): void
    {
        $this->crypter = $this->createMock(Crypter::class);
        $this->itemUpdater = new ItemUpdater($this->crypter);
    }

    public function testUpdateEncryptsData()
    {
        $this->crypter
            ->method('encrypt')
            ->willReturn('encrypted text')
        ;

        $item = new Item();
        $item->setData('foo');

        $this->itemUpdater->updateItem($item, new HiddenString('bar'));

        $this->assertSame('encrypted text', $item->getData());
    }
}
