<?php

  namespace Lassi\Server\Controllers;

  use Illuminate\Routing\Controller;
  use Lassi\Middleware\CheckVersionMiddleware;


  class ServerController extends Controller
  {

      public function __construct()
      {
          $this->middleware(CheckVersionMiddleware::class);
      }

      public function count(Request $request){
        // check request


    }

    public function sync(){

    }
  }
