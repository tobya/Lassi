<?php



return [

    'server' => [
        'url' => env('LASSI_SERVER'),
        'token_ability' => env('LASSI_TOKENABILITY', 'lassi_read'),
        'retriever' =>   Null,
        'check_ability' => env('LASSI_CHECKABILITY',true),
        ],
    'client' => [
        'token' => env('LASSI_TOKEN'),
        'server' => env('LASSI_SERVER'),
        'duplicate_email_action' => 'overwrite' , // [overwrite, ignore, error]
        'handler' => Null ,
    ],

    'version' => '0.3',




];
