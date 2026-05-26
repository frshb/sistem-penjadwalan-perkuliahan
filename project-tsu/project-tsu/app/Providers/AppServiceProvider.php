<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // <-- 1. PASTIKAN IMPORT INI ADA

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }
    public function boot(): void
    {
        Paginator::useTailwind();
    }
}
