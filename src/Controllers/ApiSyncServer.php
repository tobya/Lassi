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

    protected string $usermodel ;
    protected $lastuser_updated_at;

    public function __construct()
    {
        $this->usermodel = config('auth.providers.users.model');
    }

    /**
     * Respond to getall request.  Return just the lassi_user_id for all users.  This can be used
     * to sync very large number of users one by one.
     * @return Response
     */
    public function getall(){
        $users = $this->usermodel::all();

        $usersWithPassword = $users->map(function($user) {

          // Check for lassi guid and create if not present.
          if (!$user->lassi_user_id){
              $user->lassi_user_id =  Str::orderedUuid();
              $user->save();
          }

          return $user->lassi_user_id;
        });



        return response()->json(['status'=>200,
                                'lastitem_updated_at' => $this->lastuser_updated_at,
                                'userids_count' => $usersWithPassword->count(),
                                'userids' => $usersWithPassword]);
    }

    /**
     * @param Request $request
     * @param $lastsyncdate
     * @return void
     */
    public function count(Request $request, $lastsyncdate){
        $users = $this->getUsers($lastsyncdate);
        return response()->json(['status'=>200, 'users_count' => $users->count()??0 ]);
    }

    /**
     * Respond to sync request, retrieve all users changed after specified time and return to client.
     * @param Request $request
     * @param $lastsyncdate
     * @return mixed
     */
    public function sync(Request $request, $lastsyncdate)
    {
        $users = $this->getUsers($lastsyncdate);


        return response()->json(['status'=>200, 'lastitem_updated_at' => $this->lastuser_updated_at, 'users_count' => $users->count(),'users' => $users]);
    }

    public function getsyncids(Request $request, $lastsyncdate){
        $users = $this->getUsers($lastsyncdate);
        Log::debug($users);
        return response()->json([
            'status'=>200,
            'lastitem_updated_at' => $this->lastuser_updated_at,
            'user_ids_count' => $users->count(),
            'user_ids' => $users->pluck('lassi_user_id')]);

    }

    protected function getUsers($lastsyncdate){
       if (config('lassi.server.check_ability')){
           if (!Auth::user()->tokenCan(config('lassi.server.token_ability')))
           {
               return response('Not authorized - user does not have correct permission',401);
           }
       }

        $data = request()->input('lassidata',null);


        $startsync = Carbon::parse($lastsyncdate)->setTimeZone(config('app.timezone'));
        $endsync = now()->setTimeZone(config('app.timezone'))->subSecond();

        if (config('lassi.server.retriever')){
            $classname = config('lassi.server.retriever');
            $retriever = new $classname();
            $users = $retriever->users($startsync,$endsync, $data );
        } else {
            $users = $this->usermodel::whereBetween('updated_at',
                [$startsync,$endsync])
                ->orderby('updated_at' ,'asc')
                ->get();
        }

        $usersWithPassword = $users->map(function($user) {

              // Check for lassi guid and create if not present.
              if (!$user->lassi_user_id){
                   // Since it is possible that our retriever will have added additional attributes for transfer,
                  // we cannot save the model we recieve.  We need to retrieve fresh from db.
                  $dbuser =  $this->usermodel::find($user->id); //'($user->id);
                  $dbuser->lassi_user_id =  Str::orderedUuid();
                  $dbuser->save();
                  $user->lassi_user_id = $dbuser->lassi_user_id;
              }
              // Ensure password is sent with user info.
              $user->lassipassword = $user->password;

              return $user;
        });


         $this->lastuser_updated_at =  $endsync;
        return $usersWithPassword;
    }

    /**
     * Respond to a sync request for a specific user.  Return user info.
     * @param Request $request
     * @param $lassiuserid
     * @return Response | null
     */
    public function syncuser(Request $request, $lassiuserid){
        Log::debug($lassiuserid);
        if (config('lassi.server.retriever')){
            $classname = config('lassi.server.retriever');
            $retriever = new $classname();
            $user = $retriever->user($lassiuserid);

        } else {
            $user = $this->usermodel::where('lassi_user_id',$lassiuserid)->first();
        }
        if (!$user){

        return response()->json(['status'=>200, 'users_count' => 0,'users' => []]);
        }
          // Ensure password is sent with user info.
          $user->lassipassword = $user->password;
        return response()->json(['status'=>200, 'users_count' => 1,'users' => [$user]]);

    }

    public function updateuser(Request $request,  $lassi_userid){
        // not yet tested
        $U = $this->usermodel::where('lassi_userid',$lassi_userid);
        $update = json_decode($request->input('info'));
        if (!$U){
            $U = new $this->usermodel();
        }
        $U->name = $update->name;
        $U->email = $update->email;
        $U->password = $update->password;
        $U->save();

    }
}
