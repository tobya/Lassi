<?php

  namespace Lassi\Middleware;

  use Closure;
  use Illuminate\Http\Request;

  class CheckVersionMiddleware
  {
      public const Version = '2.0';

    public function handle(Request $request, Closure $next)
    {
       $clientVersion =  $request->header('Lassi-Version','0');
         if ($clientVersion != Static::Version ){
             return response()->json(['status' => 500 , 'error' => 'Version '. Static::Version .' expected', 'recieved' => $clientVersion],500);
         }
      return $next($request);
    }
  }
