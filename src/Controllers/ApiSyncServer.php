<?php

namespace Lassi\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;

class ApiSyncServer extends Controller
{
    public function sync($lastsyncdate)
    {
        $users = User::where('updated_at','>',Carbon::parse($lastsyncdate))->get();

        return response()->json(['status'=>200,'users' => $users]);
    }

    public function syncspecific(Request $request, $lassi_userid){

    }

    public function updateuser(Request $request){

    }
}
