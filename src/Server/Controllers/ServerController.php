<?php

  namespace Lassi\Server\Controllers;

  use Illuminate\Routing\Controller;
  use Lassi\Middleware\CheckVersionMiddleware;
  use Lassi\Server\Services\ServerService;
use Illuminate\Http\Request;
use Carbon\Carbon;

use http\Env\Response;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Lassi\Interfaces\LassiRetriever;


  class ServerController extends Controller
  {
      public ServerService $SyncService;
    protected string $usermodel ;
    protected $lastuser_updated_at;

    public function __construct()
    {
        $this->usermodel = config('auth.providers.users.model');


          $this->middleware(CheckVersionMiddleware::class);
          $this->SyncService = new ServerService();
      }

      public function syncUser($lassiuserid){
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

      public function count(Request $request){
        // check request
        $lastsyncdate = $request->input('lastsyncdate', now('utc'));
        Log::debug('lastsyncdate:'.$lastsyncdate);
        $users = $this->SyncService->getUsers($lastsyncdate);
        return response()->json(['status'=>200, 'users_count' => $users->count()??0 ]);

      }

    public function sync(Request $request){

        $lastsyncdate = $request->input('lastsyncdate', now('utc'));
        $users = $this->SyncService->getUsers($lastsyncdate);
        $result = ['status'=>200,
            // 'lastitem_updated_at' => $this->lastuser_updated_at,
             'users_count' => $users->count(),
             'users' => $users,
             'lassi_response_type' => 'users',
             ];
        Log::debug('result',$result);
         return response()->json($result);
    }

    public function syncids(Request $request){
        $lastsyncdate = $request->input('lastsyncdate', now('utc'));
        $users = $this->SyncService->getUsers($lastsyncdate)->pluck('lassi_user_id');
         return response()->json(['status'=>200,
           //  'lastitem_updated_at' => $this->lastuser_updated_at,
             'userids_count' => $users->count(),
             'userids' => $users,


             'lassi_response_type' => 'user_ids',
             ]);
    }
  }
