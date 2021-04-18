<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
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

        $secretText = 'I am in an open marriage. I just learned.';
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
        $secretText2 = 'Moses had the first tablet that could connect to the cloud.';
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

    public function testDelete()
    {
        $this->createUserAndLogin();
        $this->client->request('POST', '/item', ['data' => 'Why did the programmer need glasses? He couldn’t C#']);

        $this->client->request('GET', '/item');
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertCount(1, $response);
        $this->assertIsArray($response[0]);
        $this->assertArrayHasKey('id', $response[0]);

        $this->client->request('DELETE', '/item/' . $response[0]['id']);

        $this->client->request('GET', '/item');
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(0, $response);
    }

    public function testDeleteAnotherUsersItem()
    {
        $this->createUserAndLogin('bob');
        $this->client->request('POST', '/item', ['data' => 'If being hot was a crime, I\'d be a clean man.']);

        $this->client->request('GET', '/item');
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertCount(1, $response);
        $this->assertIsArray($response[0]);
        $this->assertArrayHasKey('id', $response[0]);

        $this->createUserAndLogin('evil_maid');
        $this->client->request('DELETE', '/item/' . $response[0]['id']);
        $this->assertResponseStatusCodeSame(400);
    }

    public function testUpdate()
    {
        $secretText1 = 'My music teacher got electrocuted yesterday.';
        $secretText2 = 'Unfortunately he was a great conductor.';

        $this->createUserAndLogin();
        $this->client->request('POST', '/item', ['data' => $secretText1]);

        $this->client->request('GET', '/item');
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertCount(1, $response);
        $this->assertIsArray($response[0]);
        $this->assertArrayHasKey('id', $response[0]);
        $itemId = $response[0]['id'];

        $this->client->request('PUT', '/item/' . $itemId, ['data' => $secretText2]);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertSame($secretText2, $response['data']);

        $this->client->request('GET', '/item');
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertCount(1, $response);
        $this->assertIsArray($response[0]);
        $this->assertArrayHasKey('data', $response[0]);
        $this->assertSame($secretText2, $response[0]['data']);
    }

    public function testUpdateAnotherUsersItem()
    {
        $this->createUserAndLogin('bob');
        $this->client->request('POST', '/item', ['data' => 'Pizza jokes are too cheesy.']);

        $this->client->request('GET', '/item');
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertCount(1, $response);
        $this->assertIsArray($response[0]);
        $this->assertArrayHasKey('id', $response[0]);

        $this->createUserAndLogin('rogue_individual');
        $this->client->request('DELETE', '/item/' . $response[0]['id']);
        $this->assertResponseStatusCodeSame(400);
    }

    private function createUserAndLogin(string $username = 'joe'): void
    {
        $this->client->loginUser($this->createUser($username));
    }

    private function createUser(string $username): User
    {
        $user = $this->userFactory->createUser($username, 'pass');
        $this->objectManager->persist($user);
        $this->objectManager->flush();

        return $user;
    }
}
