<?php

namespace Lassi\Controllers;

use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Lassi\Interfaces\LassiRetriever;

class ApiSyncServer 
{

    public function sync(Request $request, $lastsyncdate)
    {
       IF (config('lassi.server.check_ability')){
           if (!Auth::user()->tokenCan(config('lassi.server.token_ability')))
           {
               return response('Not authorized - User does not have correct permission',401);
           }
       }

        $data = request()->input('lassidata',null);

        if (config('lassi.server.retriever')){
            $classname = config('lassi.server.retriever');
            $retriever = new $classname();
            $users = $retriever->Users(Carbon::parse($lastsyncdate), $data );
        } else {
            $users = User::where('updated_at','>',Carbon::parse($lastsyncdate))->get();
        }


        $usersWithPassword = $users->map(function($user){

          // Check for lassi guid and create if not present.
          if (!$user->lassi_user_id){
               // Since it is possible that our retriever will have added additional attributes for transfer,
              // we cannot save the model we recieve.  We need to retrieve fresh from db.
              $dbuser =  config('auth.providers.users.model')::find($user->id); //'($user->id);
              $dbuser->lassi_user_id =  Str::orderedUuid();
              $dbuser->save();
              $user->lassi_user_id = $dbuser->lassi_user_id;
          }
          // Ensure password is sent with user info.
          $user->lassipassword = $user->password;
          return $user;
        });

        return response()->json(['status'=>200, 'users_count' => $usersWithPassword->count(),'users' => $usersWithPassword]);
    }

    public function syncspecific(Request $request, $lassi_userid){

    }

    public function updateuser(Request $request,  $lassi_userid){
        // not yet tested
        $U = User::where('lassi_userid',$lassi_userid);
        $update = json_decode($request->input('info'));
        if (!$U){
            $U = new User();
        }
        $U->name = $update->name;
        $U->email = $update->email;
        $U->password = $update->password;
        $U->save();

    }
}
