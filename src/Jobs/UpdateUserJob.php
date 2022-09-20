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

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Lassi\Events;
use Lassi\Events\LassiUserCreated;
use Lassi\Events\LassiUserUpdated;
use Lassi\Interfaces\LassiMapper;
use Lassi\Interfaces\LassiRetriever;

class UpdateUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

        private $guard = [
        'updated_at',
        'created_at',
        'id',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
        ];

    protected $lassiuser;

    protected $userFields;
    protected $UserModel;
    /**
     * Create a new job instance
     * @return void
     */
    public function __construct($lassiuser)
    {
        $this->lassiuser = $lassiuser;
        $this->UserModel = config('lassi.client.usermodel');

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
            $u = $this->lassiuser;


            // First check if we should deal with this user at all.
            if (!$this->shouldHandle($u)){
                return;
            }

            $user = $this->FindorCreateUser($u->lassi_user_id);
            Log::Debug('Retrieved : ' . $user->name);

            if (config('lassi.client.duplicate_email_action') == 'overwrite'){

                // if user does not match lassi_user_id and we wish to overwrite then
                // check if a user can be found matching email.
                if (!$user->exists) {
                    $emaildup = $this->UserModel::where('email',$u->email)->first();
                    if ($emaildup){
                        // set existing user
                        $user = $emaildup;
                    }
                }
            }

            $userFields = DB::getSchemaBuilder()->getColumnListing('users');
            // Loop through all fields on user table on client. Ignore specified fields and update
            // fields that exist in both client and incoming data.
            collect($userFields)->each(function ( $fieldname) use($user, $u){

                // Only attempt to set fields that are not guarded.
               if (! collect($this->guard)->contains( $fieldname)){
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
                                Log::error($msg);
            }

            if ($isNewUser){
                LassiUserCreated::dispatch($u, $user);
            }
            // updated always fires
            LassiUserUpdated::dispatch($u, $user);


    }

    public  function FindOrCreateUser($uuid){
        $user = $this->UserModel::Where('lassi_user_id','=',$uuid)->first();
        if (!$user){
            $user = new $this->UserModel();
            $user->lassi_user_id = $uuid;
        }
        return $user;

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
