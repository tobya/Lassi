<?php



return [

    'server' => [
        'url' => env('LASSI_SERVER'),
        'token_ability' => env('LASSI_TOKENABILITY', 'lassi_read'),
        'retriever' =>   Null,
        'check_ability' => env('LASSI_CHECKABILITY',true),
        ],
    'client' => [

        /**
         * ---------------------------------------------------------
         * LASSI USER MODEL
         * ---------------------------------------------------------
         * This is set to current standard model \App\Models\User if you need to specify a different Namespaced
         * user model you can provide it here.
         */
        'usermodel' => 'App\Models\User',

        /**
         * ---------------------------------------------------------
         * LASSI SERVER
         * ---------------------------------------------------------
         * Server that lassi should connect to, to request users.
         */
        'server' => env('LASSI_SERVER'),


        /**
         * ---------------------------------------------------------
         * LASSI CONNECTION TOKEN
         * ---------------------------------------------------------
         */
        'token' => env('LASSI_TOKEN'),

        /**
         * ---------------------------------------------------------
         * DUPLICATE EMAIL ACTION
         * ---------------------------------------------------------
         * When a user on the client does not yet have a matching lassi_user_id but a matching email, what action
         * should be taken
         * - Overwrite existing user
         * - Ignore the new user
         * - Raise an error.
         */
        'duplicate_email_action' => 'overwrite' , // [overwrite, ignore, error]

        /**
         * ----------------------------------------------------------
         *  Custom Handler
         * If you would like to use a custom handler to update the recieved user, specify it here.
         * Handler must implement Lassi\Interfaces\LassiSetter
         * ----------------------------------------------------------
         */
        'handler' => Null ,

        /**
         * Job queue that SyncUser requests should be added to.  Make sure that ny custom queue you add is being 
         * processed.
         */
        'queue' => 'default',
    ],

    'version' => '0.5',




];
