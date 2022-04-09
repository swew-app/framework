<?php

namespace Integration\BaseCase\stubs\controllers;

class ExampleController extends \SWEW\Framework\Controller\BaseController
{
    public function about()
    {
        $this->res('Hello world!');
    }
}
