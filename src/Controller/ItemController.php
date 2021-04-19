<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Item;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Service\DecryptingItemSerializer;
use App\Service\FormDataParser;
use App\Service\ItemFactory;
use App\Service\ItemUpdater;
use App\Service\TokenToUserResolver;
use Doctrine\Persistence\ObjectManager;
use ParagonIE\HiddenString\HiddenString;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ItemController
{
    public function __construct(
        private ObjectManager $objectManager,
        private ItemRepository $itemRepository,
        private TokenToUserResolver $tokenToUserResolver,
        private ItemFactory $itemFactory,
        private DecryptingItemSerializer $decryptingItemSerializer,
        private ItemUpdater $itemUpdater,
        private FormDataParser $formDataParser
    ) {
    }

    public function list(): JsonResponse
    {
        $result = [];
        foreach ($this->itemRepository->findByUser($this->resolveUserWithCheck()) as $item) {
            $result[] = $this->decryptingItemSerializer->serialize($item);
        }

        return new JsonResponse($result);
    }

    public function create(Request $request)
    {
        $user = $this->resolveUserWithCheck();

        $data = $request->get('data');
        if ($data === null) {
            return $this->buildMissingDataResponse();
        }

        $item = $this->itemFactory->create($user, new HiddenString($data));
        $this->objectManager->flush();

        return $this->buildItemResponse($item);
    }

    public function delete(int $id)
    {
        $item = $this->itemRepository->findOneById($id);
        if ($item === null || $item->getUser() !== $this->tokenToUserResolver->resolveUser()) {
            // Same status for both not found and forbidden to protect against guessing.
            return $this->buildItemNotFoundResponse();
        }

        $this->objectManager->remove($item);
        $this->objectManager->flush();

        return new JsonResponse(); // Should be a 204 (No Content)
    }

    public function update(Request $request)
    {
        // Too much logic in the controller, refactor.

        $itemData = $this->formDataParser->parse($request->getContent());

        if (!isset($itemData['id'])) {
            return new JsonResponse(['error' => 'Form invalid: id not found']);
        }

        if (!isset($itemData['data'])) {
            return new JsonResponse(['error' => 'Form invalid: data not found']);
        }

        $item = $this->itemRepository->findOneById((int)$itemData['id']);
        if ($item === null || $item->getUser() !== $this->tokenToUserResolver->resolveUser()) {
            // Same status for both not found and forbidden to protect against guessing.
            return $this->buildItemNotFoundResponse();
        }

        $this->itemUpdater->updateItem($item, new HiddenString($itemData['data']));
        $this->objectManager->flush();

        return $this->buildItemResponse($item);
    }

    /**
     * @return User
     *
     * @throws AccessDeniedHttpException
     */
    private function resolveUserWithCheck(): User
    {
        $user = $this->tokenToUserResolver->resolveUser();
        if ($user === null) {
            throw new AccessDeniedHttpException('Access denied');
        }

        return $user;
    }

    private function buildItemNotFoundResponse(): JsonResponse
    {
        return new JsonResponse(['error' => 'No item'], Response::HTTP_BAD_REQUEST);
    }

    private function buildMissingDataResponse(): JsonResponse
    {
        return new JsonResponse(['error' => 'No data parameter']);
    }

    private function buildItemResponse(Item $item): JsonResponse
    {
        return new JsonResponse($this->decryptingItemSerializer->serialize($item));
    }
}
