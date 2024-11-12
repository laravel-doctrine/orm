<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Testing;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Arr;

use function array_key_exists;

class ConfigRepository implements Repository
{
    /** @param mixed[] $items */
    public function __construct(private array $items)
    {
    }

    /** @return mixed[] */
    public function all(): array
    {
        return $this->items;
    }

    // phpcs:disable
    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }
    // phpcs:enable

    // phpcs:disable
    public function set($key, $value = null): void
    {
        // Pass
    }
    // phpcs:enable

    // phpcs:disable
    public function prepend($key, $value): void
    {
        // Pass
    }
    // phpcs:enable

    // phpcs:disable
    public function has($key)
    {
        return array_key_exists($key, $this->items);
    }
    // phpcs:enable

    // phpcs:disable
    public function push($key, $value): void
    {
        // Pass
    }
    // phpcs:enable
}
