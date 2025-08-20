<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Support\Arr;

use function array_merge;

class SqlsrvConnection extends Connection
{
    /**
     * @param mixed[] $settings
     *
     * @return mixed[]
     */
    public function resolve(array $settings = []): array
    {
         $overrides = [
            'driver' => 'pdo_sqlsrv',
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

        $overrides['driverOptions'] = [];
        if (isset($settings['options'])) {
            $overrides['driverOptions'] = $settings['options'];
            unset($settings['options']);
        }

        if (isset($settings['encrypt'])) {
            $overrides['driverOptions']['encrypt'] = $settings['encrypt'];
        }

        if (isset($settings['trust_server_certificate'])) {
            $overrides['driverOptions']['trustServerCertificate'] = $settings['trust_server_certificate'];
        }

        // Set default for defaultTableOptions if not present
        if (!isset($settings['defaultTableOptions'])) {
            $overrides['defaultTableOptions'] = [];
        }

        return array_merge($settings, $overrides);
    }
}
