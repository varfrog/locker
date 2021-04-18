<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\ItemRepository;
use App\Service\DecryptingItemSerializer;
use App\Service\ItemFactory;
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
        private DecryptingItemSerializer $decryptingItemSerializer
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
            return new JsonResponse(['error' => 'No data parameter']);
        }

        $item = $this->itemFactory->create($user, new HiddenString($data));
        $this->objectManager->flush();

        return new JsonResponse($this->decryptingItemSerializer->serialize($item));
    }

    public function delete(int $id)
    {
        $item = $this->itemRepository->findOneById($id);

        $noItemResponse = new JsonResponse(['error' => 'No item'], Response::HTTP_BAD_REQUEST);
        if ($item === null) {
            return $noItemResponse;
        }
        if ($item->getUser() !== $this->tokenToUserResolver->resolveUser()) {
            return $noItemResponse; // Not a 403 in order to not reveal that there is an item here.
        }

        $this->objectManager->remove($item);
        $this->objectManager->flush();

        return new JsonResponse(); // Should be a 204 (No Content)
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
}
