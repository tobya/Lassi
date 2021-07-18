<?php

namespace Lassi\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\User;

class SyncClient extends Controller
{
    public function updateusers($json)
    {
        $data  = json_decode($json);
        collect($data->users)->each(function ($u){
            $user = $this->FindorCreateUser($u->email);
            $user->name = $u->name;
            $user->email = $u->email;
            $user->password = $u->password;
            $user->save();
            echo $user->id;
        });
    }

    public function sync(){

                $client = new Client();
           $result = $client->request( 'GET',  config('lassi.server').  'api/sync/20210710');
           $json = $result->getBody()->getContents();

           $Sync = new SyncClient();
    }

    public static function FindOrCreateUser($email){
        $user = User::Where('email','=',$email)->first();
        if (!$user){
            $user = new User();
            $user->email = $email;
        }
        return $user;

    }


}
