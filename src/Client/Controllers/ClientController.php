<?php

  namespace Lassi\Client\Controllers;

  use App\Http\Controllers\Controller;
use http\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\User;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
  use Lassi\Client\Services\Httpclient;
  use Lassi\Events;
use Lassi\Events\LassiUserCreated;
use Lassi\Events\LassiUserUpdated;
use Lassi\Jobs\SyncUserJob;
use Lassi\Jobs\UpdateUserJob;
use Lassi\Middleware\CheckVersionMiddleware;


  class ClientController extends Controller
  {
      /**
       *
       * @return mixed
       */
      public function HttpClient(){
          return  Httpclient::LassiClient();
      }

      /**
       * @param $data
       * @return mixed|string|void
       */
        public function count($data = null){
        $this->currentUpdate = now('utc');

        $client = $this->httpClient();

        try {
            $url = config('lassi.server.url') .  '/lassi/count';
            $result = $client->post($url  ,[
                                            'lassidata' => json_encode(  $data),
                                            'lastsyncdate' => $this->lastUpdated(),
                                            ]
            );

            if ($result->status() == 200){

                $json = $result->getBody()->getContents();
                return  json_decode($json);
            }
        } catch ( \Exception $e) {
            $msg =  $e->getMessage();
            Log::error($msg,['trace' => $e->getTrace()]);
            return $msg;
        }

    }

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

        public  function sync($data = null){


       $this->currentUpdate = now('utc');
        $client = $this->HttpClient();

        try {
            $url = config('lassi.server.url') .  '/lassi/sync/';
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
