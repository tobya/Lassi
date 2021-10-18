<?php

namespace Lassi\Controllers;

use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApiSyncServer extends Controller
{
    public function sync($lastsyncdate)
    {
        if (!Auth::user()->tokenCan(config('lassi.token_ability'))){ return response('Not authorized',405);}

        $users = User::where('updated_at','>',Carbon::parse($lastsyncdate))->get();
       Log::debug(print_r($users,true));

        $usersWithPassword = $users->map(function($user){
          $user->lassipassword = $user->password;
          return $user;
        });

        return response()->json(['status'=>200,'users' => $usersWithPassword]);
    }

    public function syncspecific(Request $request, $lassi_userid){

    }

    public function updateuser(Request $request){

    }
}
