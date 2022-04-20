<?php

namespace Integration\BaseCase\stubs\controllers;

use Integration\BaseCase\stubs\DTO\AdminDTO;
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
        $dto = new PostDTO;

        $dto->setData(
            [
                'saved' => true,
                'id' => $this->req()->params->get('postId'),
                'text' => $this->req()->get('text'),
            ]
        );

        $this->res($dto->getData());
    }

    public function getAdmin()
    {
        $dto = $this->req()->map(new PostDTO);

        $this->res($dto->getData());
    }

    public function postAdmin()
    {
        $dto = $this->req()->map(new AdminDTO());

        $dto->validate();

        $this->res($dto->getData());
    }
}
