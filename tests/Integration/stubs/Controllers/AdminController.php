<?php

declare(strict_types=1);

namespace Swew\Testing\Integration\stubs\Controllers;

class AdminController
{
    public function getIndex(): string
    {
        return 'Hello from Admin page';
    }

    public function getSomePage(): string
    {
        return 'Some page for testing break middleware';
    }
}
