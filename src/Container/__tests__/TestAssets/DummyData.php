<?php

namespace Swew\Framework\Container\__tests__\TestAssets;

class DummyData
{
    /**
     * @param  DummyName  $name
     * @param  mixed|null  $time
     */
    public function __construct(private readonly DummyName $name, private readonly mixed $time = null)
    {
    }

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
