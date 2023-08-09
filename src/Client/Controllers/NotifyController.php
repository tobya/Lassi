<?php

  namespace Lassi\Client\Controllers;

  use App\Http\Controllers\Controller;
  use Lassi\Jobs\SyncUserJob;


  class NotifyController extends Controller
  {
    public function NotifyUserUpdate($lassiuserid){
         SyncUserJob::dispatch($lassi_user_id)->onQueue();
         return response()->json(['status' => 200, 'message' => 'notified']);
    }
  }
