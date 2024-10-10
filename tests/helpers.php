<?php

/**
 * These functions alias laravel framework functions and
 * are used in phpstan analysis
 */
function app($param) {}

function database_path($path = '') {
    return '';
}

function config_path($path = '') {
    return $path;
}

function base_path($path = '') {
    return $path;
}
