<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $cainfo = ini_get('curl.cainfo');
        if ($cainfo && file_exists($cainfo)) {
            Http::globalOptions(['verify' => $cainfo]);
        }
    }
}
