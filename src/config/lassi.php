<?php


return [

    'server' => [
        'url' => env('LASSI_SERVER'),
        'token_ability' => env('LASSI_TOKENABILITY', 'read'),
        'retriever' => '',
        ],
    'client' => [
        'token' => env('LASSI_TOKEN'),
    ],
    ''

];
