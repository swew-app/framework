<?php

declare(strict_types=1);

namespace Router\stub;

use Swew\Framework\Router\Methods\Get;

class CollectControllerStub
{
    #[Get('/main', 'Main', ['middleware_2'])]
    public function getMainPage()
    {
        return 'Main page';
    }

    #[Get('/about')]
    public function getAboutPage()
    {
        return 'About page';
    }
}
