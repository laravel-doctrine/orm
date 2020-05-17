<?php

namespace LaravelDoctrine\ORM\Testing;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Arr;

class ConfigRepository implements Repository
{
    /**
     * @var array
     */
    private $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function all()
    {
        return $this->items;
    }

    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    public function set($key, $value = null)
    {
        // Pass
    }

    public function prepend($key, $value)
    {
        // Pass
    }

    public function has($key)
    {
        return array_key_exists($key, $this->items);
    }

    public function push($key, $value)
    {
        // Pass
    }
}
