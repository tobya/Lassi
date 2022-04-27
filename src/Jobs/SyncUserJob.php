<?php

namespace Lassi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use http\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Lassi\Controllers\SyncClient;
use Lassi\Events;
use Lassi\Events\LassiUserCreated;
use Lassi\Events\LassiUserUpdated;

class SyncUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $lassiuserid;

    public $tries = 3;
    public $backoff = 10;

    /**
     * Create a new job instance
     * @return void
     */
    public function __construct($lassiuserid)
    {
        $this->lassiuserid = $lassiuserid;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->requestUser($this->lassiuserid);
        if (!$user){
            return null;
        }

        UpdateUserJob::dispatchSync($user);

    }

    public function   requestUser($lassi_user_id){

        $client = Http::withHeaders(
        [
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . config('lassi.client.token') ,
        ])->asForm();

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
        Log::debug('json:' . $json);
            $result = json_decode($result);

            Log::debug(json_last_error_msg() . json_last_error());
            if ($result->users_count == 1){
                return $result->users[0];
            }
          return null;

    }


}
