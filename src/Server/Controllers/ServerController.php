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

      public function __construct( )
      {
          $this->middleware(CheckVersionMiddleware::class);
          $this->SyncService = new ServerService();
      }

      public function count(Request $request){
        // check request
        $lastsyncdate = $request->input('lastsyncdate', now('utc'));
        $users = $this->SyncService->getUsers($lastsyncdate);
        return response()->json(['status'=>200, 'users_count' => $users->count()??0 ]);

      }

    public function sync(){
        $lastsyncdate = $request->input('lastsyncdate', now('utc'));
        $users = $this->SyncService->getUsers($lastsyncdate);
    }
  }
