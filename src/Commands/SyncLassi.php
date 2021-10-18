<?php

namespace Lassi\Commands;


use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Lassi\Controllers\SyncClient;

class SyncLassi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lassi:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


        SyncClient::sync();
        return 0;
    }



}
