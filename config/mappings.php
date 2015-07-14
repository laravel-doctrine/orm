<?php

return [

    'App\User' => [
        'type'   => 'entity',
        'table'  => 'users',
        'id'     => [
            'id' => [
                'type'     => 'integer',
                'strategy' => 'identity'
            ],
        ],
        'fields' => [
            'name' => [
                'type' => 'string'
            ]
        ]
    ]

];
