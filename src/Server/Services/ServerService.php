<?php

  namespace Lassi\Server\Services;
  use Illuminate\Routing\Controller;


use Illuminate\Http\Request;
use Carbon\Carbon;

use http\Env\Response;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Lassi\Interfaces\LassiRetriever;

  class ServerService
  {

    protected string $usermodel ;
    protected $lastuser_updated_at;
    public function __construct()
    {
        $this->usermodel = config('auth.providers.users.model');

    }

   public function getUsers($lastsyncdate){
       if (config('lassi.server.check_ability')){
           if (!Auth::user()->tokenCan(config('lassi.server.token_ability')))
           {
               return response('Not authorized - user does not have correct permission',401);
           }
       }

        $data = request()->input('lassidata',null);

        Log::debug($lastsyncdate);
        $startsync = Carbon::parse($lastsyncdate)->setTimeZone(config('app.timezone'));
        $endsync = now()->setTimeZone(config('app.timezone'))->subSecond();
        Log::debug("$startsync,$endsync");
        Log::debug(date('Ymd H:i:s'));
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

  }
