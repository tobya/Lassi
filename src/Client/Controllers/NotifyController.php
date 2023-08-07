<?php

  namespace Lassi\Client\Controllers;

  use App\Http\Controllers\Controller;
  use Illuminate\Support\Facades\RateLimiter;
  use Lassi\Jobs\SyncUserJob;


  class NotifyController extends Controller
  {
    public function NotifyUserUpdate($lassiuserid){
        $notified = true;
       // $notified = RateLimiter::attempt('lassi-notify-user',10,function (){

         SyncUserJob::dispatch($lassiuserid);

       // });
        if ($notified){
            return response()->json(['status' => 200, 'message' => 'notified']);
        } else {
            // to many attempts.
            abort(429);
        }
    }
  }
