<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use function array_merge;

class SqliteConnection extends Connection
{
    /**
     * @param mixed[] $settings
     *
     * @return mixed[]
     */
    public function resolve(array $settings = []): array
    {
        $overrides = ['driver' => 'pdo_sqlite'];

        $overrides['memory'] = $this->isMemory($settings);

        // Map Laravel keys to Doctrine DBAL keys
        if (isset($settings['database'])) {
            $overrides['path'] = $settings['database'];
            unset($settings['database']);
        }

        if (isset($settings['username'])) {
            $overrides['user'] = $settings['username'];
            unset($settings['username']);
        }

        if (isset($settings['options'])) {
            $overrides['driverOptions'] = $settings['options'];
            unset($settings['options']);
        }

        // Set default for defaultTableOptions if not present
        if (! isset($settings['defaultTableOptions'])) {
            $overrides['defaultTableOptions'] = [];
        }

        return array_merge($settings, $overrides);
    }

    /** @param mixed[] $settings */
    protected function isMemory(array $settings = []): bool
    {
        return Str::startsWith(Arr::get($settings, 'database', ''), ':memory');
    }
}
