<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Item;
use App\Entity\User;
use App\Service\ItemFactory;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ItemFactoryTest extends TestCase
{
    private ObjectManager|MockObject $objectManager;
    private ItemFactory $itemFactory;

    public function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->itemFactory = new ItemFactory($this->objectManager);
    }

    public function testCreate(): void
    {
        /** @var User */
        $user = $this->createMock(User::class);
        $data = 'secret data';

        $expectedObject = (new Item())
            ->setUser($user)
            ->setData($data)
        ;

        $this->objectManager
            ->expects($this->atLeastOnce())
            ->method('persist')
            ->with($expectedObject)
        ;

        $this->itemFactory->create($user, 'secret data');
    }
}
