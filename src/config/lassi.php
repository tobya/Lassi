<?php



return [

    'server' => [
        'url' => env('LASSI_SERVER'),
        'token_ability' => env('LASSI_TOKENABILITY', 'lassi_read'),
        'retriever' =>   Null,
        ],
    'client' => [
        'token' => env('LASSI_TOKEN'),
        'server' => env('LASSI_SERVER'),
        'duplicate_email_action' => 'overwrite' , // [overwrite, ignore, error]
    ],




];
