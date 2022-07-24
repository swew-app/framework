<?php

declare(strict_types=1);

namespace Swew\Testing\Integration\stubs\Controllers;

class AdminController
{
    // Return JSON
    public function getIndex(): void
    {
        res(['message' => 'Hello from Admin page']);
    }

    public function getSomePage(): string
    {
        return 'Some page for testing break middleware';
    }
}
