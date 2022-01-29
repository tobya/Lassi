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

    public  function updateusers($json)
    {
        $data  = json_decode($json);
        Log::debug($json);
        $userFields = DB::getSchemaBuilder()->getColumnListing('Users');
      //  dd($userFields);
        echo "Attempting to update " . $data->users_count . " users";
        $GuardedFields = collect($this->guard);
        collect($data->users)->each(function ($u) use ($userFields, $GuardedFields) {

            // First check if we should deal with this user at all.
            if (!$this->shouldHandle($u)){
                return;
            }

            $user = $this->FindorCreateUser($u->lassi_user_id);
            Log::Debug('Retrieved : ' . $user->name);
         //   dd(config('lassi.client.duplicate_email_action'));
            if (config('lassi.client.duplicate_email_action') == 'overwrite'){

                // if user does not match lassi_user_id and we wish to overwrite then
                // check if a user can be found matching email.
                if (!$user->exists) {
                    $emaildup = User::where('email',$u->email)->first();
                    if ($emaildup){
                        // set existing user
                        $user = $emaildup;
                    }
                }
            }

            // Loop through all fields on user table on client. Ignore specified fields and update
            // fields that exist in both client and incoming data.
            collect($userFields)->each(function ( $fieldname) use($user, $u, $GuardedFields){

                // Only attempt to set fields that are not guarded.
               if (!$GuardedFields->contains( $fieldname)){
                   if ($fieldname == 'password'){
                        $user->password = $u->lassipassword;
                   } else {
                       // If field exists on client user model set it.
                       if (isset($u->{$fieldname})){
                        $user->{$fieldname} =$u->{$fieldname};
                       }
                   }
               }
            });

            $isNewUser = !$user->exists;
            try {
                $user->save();
            } catch ( \Exception $e) {
                                $msg = "[Lassi] Error Happened: " . $e->getMessage() . '. Unable to create user - ' . json_encode($u);
                                echo $msg;
                                Log::error($msg);
            }

            if ($isNewUser){
                LassiUserCreated::dispatch($u, $user);
            } else {
                LassiUserUpdated::dispatch($u, $user);
            }

        });

       return  $this->writeConfig($this->currentUpdate);
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

    public  function FindOrCreateUser($uuid){
        $user = User::Where('lassi_user_id','=',$uuid)->first();
        if (!$user){
            $user = new User();
            $user->lassi_user_id = $uuid;
        }
        return $user;

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

    public function shouldHandle($user){
        if (config('lassi.client.handler')){
            $classname = config('lassi.client.handler');
            $handler = new $classname();
            return $handler->Accept($user);
        }
        return true;
    }

}
