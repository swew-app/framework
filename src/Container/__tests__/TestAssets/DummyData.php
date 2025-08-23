<?php

namespace Swew\Framework\Container\__tests__\TestAssets;

readonly class DummyData
{
    /**
     * @param  DummyName  $name
     * @param  mixed|null  $time
     */
    public function __construct(
        private DummyName $name,
        private mixed $time = null,
    ) {}

    /**
     * @return DummyName
     */
    public function getName(): DummyName
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getTime(): mixed
    {
        return $this->time;
    }
}
