<?php

declare(strict_types=1);

namespace Swew\Testing\Integration\stubs\Controllers;

class AdminController
{
    public function index(): string
    {
        return 'Hello from Admin page';
    }
}
