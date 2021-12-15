<?php


return [

    'server' => [
        'url' => env('LASSI_SERVER'),
        'token_ability' => env('LASSI_TOKENABILITY', 'lassi_read'),
        'retriever' => '',
        ],
    'client' => [
        'token' => env('LASSI_TOKEN'),
    ],
    ''

];
