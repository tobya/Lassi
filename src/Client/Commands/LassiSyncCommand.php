<?php

namespace Lassi\Client\Commands;


use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Lassi\Client\Controllers\ClientController;
use Lassi\Controllers\SyncClient;

class LassiSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lassi:sync  {--data= : Any additional data that should be passed to the lassi server. In querystring format \'a=b&c=d\' }
                                        {--all : Ignore last sync date and sync all.}
                                        {--count : get count of sync.}
                                        {--queue= : Specify Queue that user updates should be added to. If used with --all specifies sync should be performed via job queue}';

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
        $syncClient->queue = $this->option('queue','default');

        if ($this->option('count')){
          $info = (new ClientController())->count();
          $this->info( $info->users_count . ' users to be synced.');
          return;
        }

        if ($this->Option('all') == true){
            if ($this->option('queue') <> null){
                    $this->info('Adding Lassi Users to Job Queue.  Working...');
                    $UpdateInfo = $syncClient->syncAllSingle($dataArray);
            } else {

            $UpdateInfo = $syncClient->syncAll($dataArray);
            }
        } else {
            $info = (new ClientController())->count();
            $this->info($info->users_count . ' users to be synced');

            /**
             * Syncing up date/time is a bit tricky, so when count is done then sync, we need to pause
             * so that after any new users have had their lassi_user_id set by the server they are
             * ready to be retrieved.
             */
            if ($info->users_count > 0){
                sleep(2);
            }
            if ($info->users_count < 30){

                $UpdateInfo = (new ClientController() )->sync($dataArray);
            }    else {
                    $this->info('Adding Lassi Users to Job Queue.  Working...' . $info->users_count);
                    //$UpdateInfo = $syncClient->syncAllSingle($dataArray);
                    $UpdateInfo = (new ClientController() )->syncids($dataArray);
                        //->syncAllSingle($dataArray);
            }
        }

        $this->info($UpdateInfo);
        return 0;
    }




}
