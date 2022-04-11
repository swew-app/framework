<?php

namespace Integration\BaseCase\stubs\controllers;

use \Integration\BaseCase\stubs\DTO\PostDTO;


final class ExampleController extends \SWEW\Framework\Controller\BaseController
{
    public function about()
    {
        $this->res('Hello world!');
    }

    public function blog()
    {
        $this->res(
            $this->params->all()
        );
    }

    public function storePost()
    {
        $dto = $this->req(new PostDTO);

        $this->res($dto);
    }
}
