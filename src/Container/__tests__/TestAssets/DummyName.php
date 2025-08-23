<?php

namespace Swew\Framework\Container\__tests__\TestAssets;

class DummyName
{
    /**
     * @param  string  $name
     */
    public function __construct(private string $name = 'Test Name')
    {
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
