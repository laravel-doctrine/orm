<?php

if (! function_exists('storage_path')) {
    function storage_path($path = null)
    {
        $storage = __DIR__ . DIRECTORY_SEPARATOR . '../../Stubs/storage';

        return $path === null ? $storage : $storage . DIRECTORY_SEPARATOR . $path;
    }
}