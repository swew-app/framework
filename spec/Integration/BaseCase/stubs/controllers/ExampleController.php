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
        $data = [
            'saved' => true,
            'id' => $this->req()->params->get('postId'),
            'text' => $this->req()->get('text'),
        ];

        $dto = new PostDTO;

        $dto->setData($data);

        $this->res($dto);
    }

    public function getAdmin()
    {
        $dto = $this->req()->map(new PostDTO);

        $this->res($dto);
    }

    public function postAdmin()
    {
        $dto = $this->req()->map(new AdminDTO());

        $this->res($dto);
    }
}
