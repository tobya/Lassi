<?php
  namespace Lassi\Client\Services;

use Illuminate\Support\Facades\Http as coreHttpClient;

  class Httpclient extends coreHttpClient
  {
    public function LassiClient()
    {
        return Http::withHeaders(
        [
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . config('lassi.client.token') ,
            'Lassi-Version' => CheckVersionMiddleware::Version,
        ])->asForm();
    }
  }
