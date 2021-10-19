<?php

use Illuminate\Support\Facades\Route;
use Lassi\Controllers\ApiSyncServer;



Route::middleware(['auth:sanctum'])->post('lassi/sync/{lastsyncdate}/{marker?}',[ApiSyncServer::class,'sync']);

// Update details of a specific user - new password etc.
Route::middleware(['auth:sanctum'])->post('lassi/update/{lassiuserid}',[ApiSyncServer::class,'updateuser']);

