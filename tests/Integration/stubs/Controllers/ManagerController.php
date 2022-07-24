<?php

declare(strict_types=1);

namespace Swew\Testing\Integration\stubs\Controllers;

class ManagerController
{
    public function dashboard(): string
    {
        return 'Hello from Dashboard';
    }
}
