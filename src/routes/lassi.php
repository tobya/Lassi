<?php

use Illuminate\Support\Facades\Route;
use Lassi\Controllers\ApiSyncServer;


Route::get('lassi/sync/{lastsyncdate}',[ApiSyncServer::class,'sync']);
