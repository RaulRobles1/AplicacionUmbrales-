<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/* Es de la plantilla por defecto de laravel, te dice frases motivacionales (Se puede quitar sin problemas) */
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('api:sync-datos')
    ->everyFiveMinutes()
    ->withoutOverlapping(10)
    ->runInBackground();
