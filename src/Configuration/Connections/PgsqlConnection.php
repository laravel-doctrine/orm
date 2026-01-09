<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Connections;

use function array_merge;

class PgsqlConnection extends Connection
{
    /**
     * @param mixed[] $settings
     *
     * @return mixed[]
     */
    public function resolve(array $settings = []): array
    {
        $overrides = ['driver' => 'pdo_pgsql'];

        // Map Laravel keys to Doctrine DBAL keys
        if (isset($settings['database'])) {
            $overrides['dbname'] = $settings['database'];
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
}
