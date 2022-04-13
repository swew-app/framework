<?php

namespace Integration\BaseCase\stubs\controllers;

use Integration\BaseCase\stubs\DTO\PostDTO;


final class ExampleController extends \SWEW\Framework\Base\BaseController
{
    public function about()
    {
        $this->res('Hello world!');
    }

    public function blog()
    {
        $this->res(
            $this->app->req->params->all()
        );
    }

    public function storePost()
    {
        $dto = $this->req(new PostDTO);

        $this->res($dto);
    }

    public function getAdmin()
    {
        $dto = $this->req(new PostDTO);

        $this->res($dto);
    }
}
