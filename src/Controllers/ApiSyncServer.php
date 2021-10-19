<?php

namespace Lassi\Controllers;

use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Lassi\Interfaces\LassiRetriever;

class ApiSyncServer extends Controller
{

    public function sync($lastsyncdate, $marker = '')
    {
        if (!Auth::user()->tokenCan(config('lassi.server.token_ability'))){ return response('Not authorized',401);}

        if (config('lassi.server.retriever') <> ''){
            $classname = config('lassi.server.retriever');
            $retriever = new $classname();
            $users = $retriever->UsersFor(Carbon::parse($lastsyncdate), $marker);
        } else {
            $users = User::where('updated_at','>',Carbon::parse($lastsyncdate))->get();
        }


        $usersWithPassword = $users->map(function($user){
          $user->lassipassword = $user->password;
          return $user;
        });

        return response()->json(['status'=>200,'users' => $usersWithPassword]);
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
