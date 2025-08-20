<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Support\Arr;

class OracleConnection extends Connection
{
    /**
     * @param mixed[] $settings
     *
     * @return mixed[]
     */
    public function resolve(array $settings = []): array
    {
        $overrides = [
            'driver' => 'oci8',
        ];

        // Map Laravel keys to Doctrine DBAL keys
        if (isset($settings['database'])) {
            $overrides['dbname'] = $settings['database'];
            unset($settings['database']);
        }

        if (isset($settings['username'])) {
            $overrides['user'] = $settings['username'];
            unset($settings['username']);
        }

        if (isset($settings['service_name'])) {
            $overrides['servicename'] = $settings['service_name'];
            unset($settings['service_name']);
        }

        if (isset($settings['options'])) {
            $overrides['driverOptions'] = $settings['options'];
            unset($settings['options']);
        }

        // Set default for defaultTableOptions if not present
        if (!isset($settings['defaultTableOptions'])) {
            $overrides['defaultTableOptions'] = [];
        }

        return array_merge($settings, $overrides);
    }
}
