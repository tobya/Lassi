<?php

use Illuminate\Support\Facades\Route;
use Lassi\Controllers\ApiSyncServer;



Route::middleware(['auth:sanctum'])->post('lassi/sync/{lastsyncdate}',[ApiSyncServer::class,'sync']);

