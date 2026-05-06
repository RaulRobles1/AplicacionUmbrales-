<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        View::composer('auth.plantilla', function ($view) {
            $ccaa = DB::table('umbrales_ccaa')
                ->select('c_id', 'c_comunidad_autonoma')
                ->orderBy('c_comunidad_autonoma', 'asc')
                ->get();
            $view->with('todasLasCcaa', $ccaa);
        });
    }
}
