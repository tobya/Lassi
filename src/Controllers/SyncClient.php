<?php

namespace Lassi\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        collect($data->users)->each(function ($u) {
            $user = $this->FindorCreateUser($u->email);
            $user->name = $u->name;
            $user->email = $u->email;
            log::debug('collecting');
            collect($user->getAttributes())->each(function ($fieldvalue, $fieldname) use($user, $u){
                log::debug('allattributes');
               if (!collect($this->guard)->contains( $fieldname)){

                   if ($fieldname == 'password'){
                        $user->password = $u->lassipassword;
                   } else {
                       if (isset($u->{$fieldname})){

                       $user->{$fieldname} =$u->{$fieldname};
                        log::debug('no guard' . $fieldname . '=' . $u->{$fieldname});
                       }
                   }
               }
            });

            $user->save();

            echo $user->id;
        });

        $this->writeConfig($this->currentUpdate);
    }

    public  function sync(){
                $this->currentUpdate = $this->lastUpdated();
                $client = new Client();
                $headers = [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . config('lassi.token') ,
                ];

        try {
        echo "Attempting to sync users from " . $this->lastUpdated() . "\n";
        $result = $client->post(config('lassi.server').  '/lassi/sync/' . $this->lastUpdated(),['headers' => $headers]);
        } catch ( \Exception $e) {
            return "Error Happened :" . $e->getMessage();
        }

           $json = $result->getBody()->getContents();
           Log::debug($json);
          return  $this->updateusers($json);

    }

    public  function FindOrCreateUser($email){
        $user = User::Where('email','=',$email)->first();
        if (!$user){
            $user = new User();
            $user->email = $email;
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

}
