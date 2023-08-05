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
    public  function LassiClient()
    {
        return Http::withHeaders(
        [
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . config('lassi.client.token') ,
            'Lassi-Version' => CheckVersionMiddleware::Version,
        ])->asForm();
    }

      /**
       * @param $data
       * @return mixed|string|void
       */
        public function count($data = null){
        

        $client = $this->LassiClient();

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
        $client = $this->LassiClient();

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
            $lastupdate = $lastupdate->toW3cString();
        }
        echo "\n" . $lastupdate;
        Cache::put('lassi.config', ['lastupdate' => $lastupdate]);

    }

    public function lastUpdated()
    {
      $value =  Cache::get('lassi.config',['lastupdate' => '20000101']);
        return $value['lastupdate'];
    }

    public function syncids(){
            return $this->syncToQueue($this->lastUpdated());
    }

    public function syncToQueue($updatefromdate){
            $this->currentUpdate = now('utc');
        $client = $this->Lassiclient();

        try {

            $result = $client->post(config('lassi.server.url') .  '/lassi/sync/ids',[  'lastsyncdate' => $updatefromdate,]);

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
               SyncUserJob::dispatch($lassi_user_id)->onQueue(config('lassi.client.queue'));
            });



            $this->writeConfig($this->currentUpdate);
        return 'Added ' . $UserList->userids_count . ' ids to Job list';
    }
 public function syncAllSingle(){

        $this->currentUpdate = now('utc');

        $client = $this->Lassiclient();

        try {

            $result = $client->post(config('lassi.server.url') .  '/lassi/get/all',[
                ''
            ]);

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
               SyncUserJob::dispatch($lassi_user_id)->onQueue(config('lassi.client.queue'));
            });



            $this->writeConfig($this->currentUpdate);
        return 'Added ' . $UserList->userids_count . ' ids to Job list';
    }

    public function requestUser($lassi_user_id){

            $client = $this->LassiClient();

        try {

            $result = $client->post(config('lassi.server.url') .  '/lassi/sync/user/' . $lassi_user_id );

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

            $result = json_decode($result);

          //  Log::debug(json_last_error_msg() . json_last_error());
            if ($result->users_count == 1){
                return $result->users[0];
            }
          return null;
    }

  }
