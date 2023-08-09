<?php

namespace Lassi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use http\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Lassi\Client\Controllers\ClientController;
use Lassi\Controllers\SyncClient;
use Lassi\Events;
use Lassi\Events\LassiUserCreated;
use Lassi\Events\LassiUserUpdated;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Queue\Middleware\RateLimited;

class SyncUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $lassiuserid;

    public $tries = 10;
    public $backoff = 10;

    /**
     * Create a new job instance
     * @return void
     */
    public function __construct($lassiuserid)
    {
        $this->lassiuserid = $lassiuserid;

    }

    /**
     * Add any required middleware
     * @return RateLimited[]
     */
    public function middleware(){
        // if Ratelimiter exists apply it.
        return [new RateLimited('lassi-updates')];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = app(ClientController::class)->requestUser($this->lassiuserid);

        if (!$user){
            return null;
        }

        UpdateUserJob::dispatchSync($user);

    }




}
