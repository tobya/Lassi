<?php

  namespace Lassi\Client\Commands;

  use Illuminate\Console\Command;
  use Lassi\Client\Controllers\ClientController;

  class LastUpdatedCommand extends Command
  {
    protected $signature = 'lassit:lastupdated';

    protected $description = 'Display the datetime last synced with server.';

    public function handle(): void
    {
      $this->info(app(ClientController::class)->lastUpdated());
      
    }
  }
