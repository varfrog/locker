<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Item;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Service\DecryptingItemSerializer;
use App\Service\ItemFactory;
use App\Service\TokenToUserResolver;
use Doctrine\Persistence\ObjectManager;
use ParagonIE\HiddenString\HiddenString;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

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

    /**
     * @Route("/item/{id}", name="items_delete", methods={"DELETE"})
     * @IsGranted("ROLE_USER")
     */
    public function delete(Request $request, int $id)
    {
        if (empty($id)) {
            return $this->json(['error' => 'No data parameter'], Response::HTTP_BAD_REQUEST);
        }

        $item = $this->getDoctrine()->getRepository(Item::class)->find($id);

        if ($item === null) {
            return $this->json(['error' => 'No item'], Response::HTTP_BAD_REQUEST);
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($item);
        $manager->flush();

        return $this->json([]);
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
