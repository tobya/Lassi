<?php

namespace Lassi\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Routing\Controller as BaseController;

class SyncClient extends BaseController
{
    public static function updateusers($json)
    {
        $data  = json_decode($json);
        collect($data->users)->each(function ($u){
            $user = self::FindorCreateUser($u->email);
            $user->name = $u->name;
            $user->email = $u->email;
            $user->password = $u->lassipassword;
            $user->save();
            echo $user->id;
        });
    }

    public static function sync(){

                $client = new Client();
           $result = $client->request( 'GET',  config('lassi.server').  '/lassi/sync/20211010');
           $json = $result->getBody()->getContents();
            self::updateusers($json);

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
