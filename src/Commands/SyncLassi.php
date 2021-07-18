<?php

namespace Lassi\Commands;

use App\Http\Controllers\SyncClient;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

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
        $client = new Client();
           $result = $client->request( 'GET', 'http://127.0.0.1:8000/api/sync/20210710');
           $json = $result->getBody()->getContents();
           $this->info($json);
           $Sync = new SyncClient();
        $Sync->updateusers($json);
        return 0;
    }


}
