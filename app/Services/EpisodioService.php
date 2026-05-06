<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EpisodioService
{
    // Comunidades autónomas con episodios
    public function obtenerComunidadesConEpisodios()
    {
        return Cache::remember('episodios_comunidades_con_episodios_v1', now()->addMinutes(30), function () {
            return DB::table('umbrales_ccaa')
                ->join('umbrales_ranepisodio', 'umbrales_ccaa.c_id', '=', 'umbrales_ranepisodio.re_ccaa_id')
                ->select('umbrales_ccaa.c_id', 'umbrales_ccaa.c_comunidad_autonoma as nombre')
                ->distinct()
                ->orderBy('nombre', 'asc')
                ->get();
        });
    }
}
