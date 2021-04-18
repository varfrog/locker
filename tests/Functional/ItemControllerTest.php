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
        $this->createUserAndLogin();

        $secretText = 'Moses had the first tablet that could connect to the cloud';
        $this->client->request('POST', '/item', ['data' => $secretText]);
        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertSame($secretText, $response['data']);
    }

    public function testList()
    {
        $this->createUserAndLogin();

        $secretText1 = 'Epstein is like a full garbage bag. It’s not gonna take itself out.';
        $secretText2 = 'Why did the programmer need glasses? He couldn’t C#.';
        $this->client->request('POST', '/item', ['data' => $secretText1]);
        $this->client->request('POST', '/item', ['data' => $secretText2]);

        $this->client->request('GET', '/item');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($response);
        $this->assertCount(2, $response);

        $this->assertIsArray($response[0]);
        $this->assertIsArray($response[1]);

        $this->assertArrayHasKey('id', $response[0]);

        $this->assertArrayHasKey('created_at', $response[0]);
        $this->assertArrayHasKey('created_at', $response[1]);

        $this->assertArrayHasKey('updated_at', $response[0]);
        $this->assertArrayHasKey('updated_at', $response[1]);

        $this->assertArrayHasKey('data', $response[0]);
        $this->assertArrayHasKey('data', $response[1]);

        $this->assertSame($secretText1, $response[0]['data']);
        $this->assertSame($secretText2, $response[1]['data']);
    }

    private function createUserAndLogin(): void
    {
        $user = $this->userFactory->createUser('joe', 'pass');
        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $this->client->loginUser($user);
    }
}
