<?php

namespace Lassi\Controllers;

use http\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Lassi\Events;
use Lassi\Events\LassiUserCreated;
use Lassi\Events\LassiUserUpdated;
use Lassi\Jobs\SyncUserJob;
use Lassi\Jobs\UpdateUserJob;


class SyncClient extends BaseController
{
    private $guard = [
        'updated_at',
        'created_at',
        'id',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
        ];
    public $queue = null;

    public  function updateusers($json)
    {
        $data  = json_decode($json);
        Log::debug($json);

      //  dd($userFields);
        echo "Attempting to update " . $data->users_count . " users. Pushed onto Queue.";

        collect($data->users)->each(function ($u)  {
            UpdateUserJob::dispatch($u);
        });

       return  $this->writeConfig($this->currentUpdate);
    }

    public function syncAll($data = null){
        $this->writeConfig('19000101');
        return $this->sync($data);
    }

    public function syncAllSingle(){

        $this->currentUpdate = now('utc');

        $client = Http::withHeaders(
        [
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . config('lassi.client.token') ,
        ])->asForm();

        try {

            $result = $client->post(config('lassi.server.url') .  '/lassi/get/all');

            if ($result->status() <> 200){
              Log::error('[Lassi:sync] Error Occurred: '. $result->status());
              abort($result->status(),'Error Returned: ' . $result->status() . ' ' .  $result->getBody()->getContents());
            }

        } catch ( \Exception $e) {
            $msg =  $e->getMessage();
            Log::error($msg,['trace' => $e->getTrace()]);
            return $msg;
        }

        $json = $result->getBody()->getContents();

        $UserList = json_decode($json);
        collect($UserList->userids)->each(function ($lassi_user_id){
           SyncUserJob::dispatch($lassi_user_id)->onQueue($this->queue);
        });



        $this->writeConfig($this->currentUpdate);
        return 'Added ' . $UserList->userids_count . ' ids to Job list';
    }



    public  function sync($data = null){
       $this->currentUpdate = now();
        $client = Http::withHeaders(
        [
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . config('lassi.client.token') ,
        ])->asForm();

        try {

            $result = $client->post(config('lassi.server.url') .  '/lassi/sync/' . urlencode( $this->lastUpdated())
                                ,['lassidata' => json_encode(  $data)]);

            if ($result->status() <> 200){
              Log::error('[Lassi:sync] Error Occurred: '. $result->status());
              abort($result->status(),'Error Returned: ' . $result->status() . ' ' .  $result->getBody()->getContents());
            }

        } catch ( \Exception $e) {
            $msg =  $e->getMessage();
            Log::error($msg,['trace' => $e->getTrace()]);
            return $msg;
        }

           $json = $result->getBody()->getContents();

          return  $this->updateusers($json);

    }



    public  function writeConfig($lastupdate){
        $configfile = storage_path('app/lassi/lassi.config');
        if (file_exists($configfile)){
            $file = File::get($configfile);
            $config = json_decode($file,true);
        } else {
            File::ensureDirectoryExists(pathinfo($configfile,PATHINFO_DIRNAME));
            $config = [];
        }
        $config['lastupdate'] = $lastupdate;
        File::put($configfile, json_encode($config));
        return $configfile;
    }

    /**
     *
     * @return mixed|string
     */
    public function lastUpdated()
    {
        $configfn = storage_path('app/lassi/lassi.config');
        if (file_exists($configfn)){
            $file = File::get($configfn);
            $config = json_decode($file,true);
          //  dd($config);
            return $config['lastupdate'];
        } else {
            return '19000101';
        }
    }



}
