<?php

declare(strict_types=1);

namespace Swew\Testing\Integration\stubs\Controllers;

class ExampleController
{
    public function getIndex(): void
    {
        res('Hello world!');
    }

    public function getPost(): void
    {
        res('Hello world from POST page!');
    }

    public function postPost(): void
    {
        $arr = req()->input();

        res(
            implode(', ', $arr)
        );
    }
}
