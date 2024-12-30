<?php

declare(strict_types=1);

namespace Router\stub;

use Swew\Framework\Router\Attribute\Route;

class CollectControllerStub
{
    #[Route('/main', 'Main', methods: ['GET'], middlewares: ['middleware_2'])]
    public function getMainPage(): string
    {
        return 'Main page';
    }

    #[Route('/about', methods: ['GET'])]
    public function getAboutPage(): string
    {
        return 'About page';
    }
}
