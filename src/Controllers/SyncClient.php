<?php

namespace Lassi\Controllers;

use http\Client;
use Illuminate\Support\Facades\Cache;
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
use Lassi\Middleware\CheckVersionMiddleware;


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
        echo "Pushing " . $data->users_count . " user update jobs on to the Queue.";

        $i = 0;
        collect($data->users)->each(function ($u) use(&$i)  {
            $i++;
            UpdateUserJob::dispatch($u)->onQueue(config('lassi.client.queue'));

            if ($i % 100 == 0){
                echo '.';
            }
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

    public function count($data = null){
        $this->currentUpdate = now('utc');
        $client = Http::withHeaders(
        [
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . config('lassi.client.token') ,
            'Lassi-Version' => CheckVersionMiddleware::Version,
        ])->asForm();

        try {
            $url = config('lassi.server.url') .  '/lassi/count';
            $result = $client->post($url
                                ,[
                                    'lassidata' => json_encode(  $data),
                                    'lastsyncdate' => $this->lastUpdated(),
                                ]
            );

            if ($result->status() <> 200){

            }
        } catch ( \Exception $e) {
            $msg =  $e->getMessage();
            Log::error($msg,['trace' => $e->getTrace()]);
            return $msg;
        }

           $json = $result->getBody()->getContents();

          return  $json;
    }


    public  function sync($data = null){
       $this->currentUpdate = now('utc');
        $client = Http::withHeaders(
        [
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . config('lassi.client.token') ,
            'Lassi-Version' => CheckVersionMiddleware::Version,
        ])->asForm();

        try {
            $url = config('lassi.server.url') .  '/lassi/sync';
            
            $result = $client->post($url
                                ,[
                                    'lassidata' => json_encode(  $data),
                                    'lastsyncdate' => $this->lastUpdated(),
                ]);

            if ($result->status() <> 200){
              Log::error('[Lassi:sync] Error Occurred: '. $result->status());
              Log::info('[Lassi:sync] ' . $url  );
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
        if (is_object($lastupdate)){
            // must be a string, and cannot contain spaces - garbled by urlencoding.
            $lastupdate = $lastupdate->format('Ymd\TH:i:s');
        }
        Cache::put('lassi.config', ['lastupdate' => $lastupdate]);

    }

    public function lastUpdated()
    {
      $value =  Cache::get('lassi.config',['lastupdate' => '20000101']);
        return $value['lastupdate'];
    }



}
