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
    protected $signature = 'lassi:sync  {--data= : Any additional data that should be passed to the lassi server. In querystring format \'a=b&c=d\' }
                                        {--all : Ignore last sync date and sync all.}
                                        {--single : Sync All individually. useful with large numbers}
                                        {--queue=default : Specify Queue that user updates should be added to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync with Lassi Server';

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
        // data can be passed in like a query string value1=1&V2=3
        parse_str($this->option('data'),$dataArray);


        $syncClient = new SyncClient();
        $syncClient->queue = $this->option('queue');
        if ($this->Option('all') == true){
            if ($this->option('single') == true){

            $UpdateInfo = $syncClient->syncAllSingle($dataArray);
            } else {

            $UpdateInfo = $syncClient->syncAll($dataArray);
            }
        } else {
            $UpdateInfo = $syncClient->sync($dataArray);
        }

        $this->info($UpdateInfo);
        return 0;
    }



}
