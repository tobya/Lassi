<?php

use Illuminate\Support\Facades\Route;
use Lassi\Controllers\ApiSyncServer;



Route::middleware(['auth:sanctum'])->post('lassi/get/all',[ApiSyncServer::class,'getall']);
Route::middleware(['auth:sanctum'])->post('lassi/sync/user/{lassiuserid}',[ApiSyncServer::class,'syncuser']);
Route::middleware(['auth:sanctum'])->get ('lassi/sync/user/{lassiuserid}',[ApiSyncServer::class,'syncuser']);
Route::middleware(['auth:sanctum'])->get ('lassi/sync/all',[ApiSyncServer::class,'syncall']);
Route::middleware(['auth:sanctum'])->get ('lassi/sync/ids/{lastsyncdate}',[ApiSyncServer::class,'getsyncids']);
Route::middleware(['auth:sanctum'])->post ('lassi/sync/ids/{lastsyncdate}',[ApiSyncServer::class,'getsyncids']);
Route::middleware(['auth:sanctum'])->get ('lassi/sync/count/{lastsyncdate}',[ApiSyncServer::class,'count']);
Route::middleware(['auth:sanctum'])->post ('lassi/sync/count/{lastsyncdate}',[ApiSyncServer::class,'count']);
Route::middleware(['auth:sanctum'])->post('lassi/sync/{lastsyncdate}/{marker?}',[ApiSyncServer::class,'sync']);
Route::get('lassi/sync/{lastsyncdate}/{marker?}',[ApiSyncServer::class,'sync']);


// Update details of a specific user - new password etc.
Route::middleware(['auth:sanctum'])->post('lassi/update/{lassiuserid}',[ApiSyncServer::class,'updateuser']);

