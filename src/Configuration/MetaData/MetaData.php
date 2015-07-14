<?php

namespace Brouwers\LaravelDoctrine\Configuration\MetaData;

use Brouwers\LaravelDoctrine\Configuration\Driver;

interface MetaData extends Driver
{
    /**
     * @param array $settings
     * @param bool  $dev
     *
     * @return static
     */
    public function configure(array $settings = [], $dev = false);

    /**
     * @return mixed
     */
    public function getCache();
}
