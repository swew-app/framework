<?php

namespace Swew\Testing\Container\TestAssets;

class DummyName
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @param  string  $name
     */
    public function __construct(string $name = 'Test Name')
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function get(): string
    {
        return $this->name;
    }

    /**
     * @param  string  $name
     */
    public function set(string $name): void
    {
        $this->name = $name;
    }
}
