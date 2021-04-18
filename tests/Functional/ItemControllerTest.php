<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Service\UserFactory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ItemControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ObjectManager $objectManager;
    private UserFactory $userFactory;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();

        /** @var ObjectManager $entityManager */
        $entityManager = $container->get('doctrine.orm.default_entity_manager');

        /** @var UserFactory $userFactory */
        $userFactory = $container->get('app.service.user_factory');

        $this->objectManager = $entityManager;
        $this->userFactory = $userFactory;
    }

    public function testCreate()
    {
        $user = $this->userFactory->createUser('joe', 'pass');
        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $this->client->loginUser($user);
        $data = 'Moses had the first tablet that could connect to the cloud';

        $this->client->request('POST', '/item', ['data' => $data]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString($data, $this->client->getResponse()->getContent()); // todo check for array key
    }

    public function testList()
    {

    }
}
