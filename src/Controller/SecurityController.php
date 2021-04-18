<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\TokenToUserResolver;
use Symfony\Component\HttpFoundation\JsonResponse;

class SecurityController
{
    public function __construct(private TokenToUserResolver $tokenToUserResolver)
    {
    }

    public function login(): JsonResponse
    {
        $user = $this->tokenToUserResolver->resolveUser();

        return new JsonResponse([
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
    }
}
