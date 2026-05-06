<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BuscadorService
{
    private const MAX_RESULTADOS_POR_BLOQUE = 200;

    public function BuscarGlobal($query): array
    {
        $queryNormalizada = $this->normalizarTexto((string) $query);
        if ($queryNormalizada === '') {
            return $this->respuestaVacia();
        }

        $tokens = $this->extraerTokens($queryNormalizada);
        $filtros = $this->extraerFiltros($queryNormalizada, $tokens);
        $cacheKey = 'busqueda_global_v2:' . md5($queryNormalizada);

        return Cache::remember($cacheKey, now()->addSeconds(90), function () use ($queryNormalizada, $tokens, $filtros) {
            $ultimosDatos = DB::table('umbrales_randatosepisodio')
                ->select(DB::raw('DISTINCT ON (rde_estacion) rde_estacion, rde_valor, rde_valor_accesorio, rde_hora'))
                ->orderBy('rde_estacion')
                ->orderBy('rde_hora', 'desc');

            $embalses = $this->debeBuscarTipo('embalse', $filtros['tipos'])
                ? $this->buscarEmbalses($ultimosDatos, $queryNormalizada, $tokens, $filtros)
                : collect();
            $roeas = $this->debeBuscarTipo('roea', $filtros['tipos'])
                ? $this->buscarRoeas($ultimosDatos, $queryNormalizada, $tokens, $filtros)
                : collect();
            $marcosControl = $this->debeBuscarTipo('marco', $filtros['tipos'])
                ? $this->buscarMarcosControl($ultimosDatos, $queryNormalizada, $tokens, $filtros)
                : collect();
            $aforos = $this->debeBuscarTipo('aforo', $filtros['tipos'])
                ? $this->buscarAforos($ultimosDatos, $queryNormalizada, $tokens, $filtros)
                : collect();
            $episodios = $this->debeBuscarTipo('episodio', $filtros['tipos'])
                ? $this->buscarEpisodios($queryNormalizada, $tokens, $filtros)
                : collect();

            return [
                'embalses' => $embalses,
                'roeas' => $roeas,
                'marcos_control' => $marcosControl,
                'aforos' => $aforos,
                'episodios' => $episodios,
                'meta' => [
                    'query' => $queryNormalizada,
                    'total' => $embalses->count() + $roeas->count() + $marcosControl->count() + $aforos->count() + $episodios->count(),
                ],
            ];
        });
    }

    private function buscarEmbalses($ultimosDatos, string $query, array $tokens, array $filtros)
    {
        $consulta = DB::table('umbrales_embalsesran as e')
            ->leftJoinSub($ultimosDatos, 'd', 'e.er_codigo', '=', 'd.rde_estacion')
            ->leftJoin('umbrales_ccaa as c', 'e.er_comunidad_autonoma_id', '=', 'c.c_id')
            ->select(
                'e.*',
                'd.rde_valor',
                'd.rde_valor_accesorio',
                'd.rde_hora',
                'c.c_comunidad_autonoma as ccaa_nombre',
                DB::raw('CASE WHEN d.rde_valor >= e.er_umbral3 THEN 3 WHEN d.rde_valor >= e.er_umbral2 THEN 2 WHEN d.rde_valor >= e.er_umbral1 THEN 1 ELSE 0 END as nivel_alerta')
            );

        $this->aplicarBusquedaTexto($consulta, [
            'e.er_nombre',
            'e.er_codigo',
            'e.er_rio',
            'e.er_municipio',
            'e.er_provincia',
            'e.er_tag_ip21',
            'e.er_tag_volumen',
            'e.er_tag_digital_ip21',
            'c.c_comunidad_autonoma',
        ], $tokens);

        $this->aplicarFiltroNivel($consulta, 'CASE WHEN d.rde_valor >= e.er_umbral3 THEN 3 WHEN d.rde_valor >= e.er_umbral2 THEN 2 WHEN d.rde_valor >= e.er_umbral1 THEN 1 ELSE 0 END', $filtros['nivel']);
        $this->aplicarFiltroActivo($consulta, 'e.er_activo', $filtros['solo_activos'], $filtros['solo_inactivos']);

        $this->aplicarPuntuacion($consulta, [
            'e.er_codigo' => 90,
            'e.er_nombre' => 75,
            'e.er_rio' => 45,
            'e.er_municipio' => 35,
            'e.er_provincia' => 35,
            'e.er_tag_ip21' => 60,
            'e.er_tag_volumen' => 50,
            'c.c_comunidad_autonoma' => 40,
        ], $query, $tokens);

        return $consulta
            ->orderByDesc('score')
            ->orderByDesc('nivel_alerta')
            ->orderBy('e.er_nombre')
            ->limit(self::MAX_RESULTADOS_POR_BLOQUE)
            ->get();
    }

    private function buscarRoeas($ultimosDatos, string $query, array $tokens, array $filtros)
    {
        $consulta = DB::table('umbrales_umbralesran as ur')
            ->leftJoinSub($ultimosDatos, 'd', 'ur.ur_codigo', '=', 'd.rde_estacion')
            ->leftJoin('umbrales_ccaa as c', 'ur.ur_comunidad_autonoma_id', '=', 'c.c_id')
            ->where('ur.ur_codigo', 'LIKE', 'R0%')
            ->select(
                'ur.*',
                'd.rde_valor',
                'd.rde_valor_accesorio',
                'd.rde_hora',
                'c.c_comunidad_autonoma as ccaa_nombre',
                DB::raw('CASE WHEN d.rde_valor >= ur.ur_umbral3 THEN 3 WHEN d.rde_valor >= ur.ur_umbral2 THEN 2 WHEN d.rde_valor >= ur.ur_umbral1 THEN 1 ELSE 0 END as nivel_alerta')
            );

        $this->aplicarBusquedaTexto($consulta, [
            'ur.ur_nombre',
            'ur.ur_codigo',
            'ur.ur_rio',
            'ur.ur_zona_explotacion',
            'ur.ur_provincia',
            'ur.ur_tag_ip21',
            'ur.ur_tag_ip21_caudal',
            'ur.ur_tag_digital_ip21',
            'c.c_comunidad_autonoma',
        ], $tokens);

        $this->aplicarFiltroNivel($consulta, 'CASE WHEN d.rde_valor >= ur.ur_umbral3 THEN 3 WHEN d.rde_valor >= ur.ur_umbral2 THEN 2 WHEN d.rde_valor >= ur.ur_umbral1 THEN 1 ELSE 0 END', $filtros['nivel']);
        $this->aplicarFiltroActivo($consulta, 'ur.ur_activo', $filtros['solo_activos'], $filtros['solo_inactivos']);

        $this->aplicarPuntuacion($consulta, [
            'ur.ur_codigo' => 95,
            'ur.ur_nombre' => 70,
            'ur.ur_rio' => 45,
            'ur.ur_zona_explotacion' => 35,
            'ur.ur_tag_ip21' => 65,
            'ur.ur_tag_ip21_caudal' => 40,
            'c.c_comunidad_autonoma' => 40,
        ], $query, $tokens);

        return $consulta
            ->orderByDesc('score')
            ->orderByDesc('nivel_alerta')
            ->orderBy('ur.ur_nombre')
            ->limit(self::MAX_RESULTADOS_POR_BLOQUE)
            ->get();
    }

    private function buscarMarcosControl($ultimosDatos, string $query, array $tokens, array $filtros)
    {
        $consulta = DB::table('umbrales_umbralesran as ur')
            ->leftJoinSub($ultimosDatos, 'd', 'ur.ur_codigo', '=', 'd.rde_estacion')
            ->leftJoin('umbrales_ccaa as c', 'ur.ur_comunidad_autonoma_id', '=', 'c.c_id')
            ->where('ur.ur_codigo', 'LIKE', 'MC%')
            ->select(
                'ur.*',
                'd.rde_valor',
                'd.rde_valor_accesorio',
                'd.rde_hora',
                'c.c_comunidad_autonoma as ccaa_nombre',
                DB::raw('CASE WHEN d.rde_valor >= ur.ur_umbral3 THEN 3 WHEN d.rde_valor >= ur.ur_umbral2 THEN 2 WHEN d.rde_valor >= ur.ur_umbral1 THEN 1 ELSE 0 END as nivel_alerta')
            );

        $this->aplicarBusquedaTexto($consulta, [
            'ur.ur_nombre',
            'ur.ur_codigo',
            'ur.ur_rio',
            'ur.ur_zona_explotacion',
            'ur.ur_provincia',
            'ur.ur_tag_ip21',
            'ur.ur_tag_ip21_caudal',
            'ur.ur_tag_digital_ip21',
            'c.c_comunidad_autonoma',
        ], $tokens);

        $this->aplicarFiltroNivel($consulta, 'CASE WHEN d.rde_valor >= ur.ur_umbral3 THEN 3 WHEN d.rde_valor >= ur.ur_umbral2 THEN 2 WHEN d.rde_valor >= ur.ur_umbral1 THEN 1 ELSE 0 END', $filtros['nivel']);
        $this->aplicarFiltroActivo($consulta, 'ur.ur_activo', $filtros['solo_activos'], $filtros['solo_inactivos']);

        $this->aplicarPuntuacion($consulta, [
            'ur.ur_codigo' => 95,
            'ur.ur_nombre' => 70,
            'ur.ur_rio' => 45,
            'ur.ur_zona_explotacion' => 35,
            'ur.ur_tag_ip21' => 65,
            'ur.ur_tag_ip21_caudal' => 40,
            'c.c_comunidad_autonoma' => 40,
        ], $query, $tokens);

        return $consulta
            ->orderByDesc('score')
            ->orderByDesc('nivel_alerta')
            ->orderBy('ur.ur_nombre')
            ->limit(self::MAX_RESULTADOS_POR_BLOQUE)
            ->get();
    }

    private function buscarAforos($ultimosDatos, string $query, array $tokens, array $filtros)
    {
        $consulta = DB::table('umbrales_umbralesran as ur')
            ->leftJoinSub($ultimosDatos, 'd', 'ur.ur_codigo', '=', 'd.rde_estacion')
            ->leftJoin('umbrales_ccaa as c', 'ur.ur_comunidad_autonoma_id', '=', 'c.c_id')
            ->where('ur.ur_codigo', 'LIKE', 'AR%')
            ->select(
                'ur.*',
                'd.rde_valor',
                'd.rde_valor_accesorio',
                'd.rde_hora',
                'c.c_comunidad_autonoma as ccaa_nombre',
                DB::raw('CASE WHEN d.rde_valor >= ur.ur_umbral3 THEN 3 WHEN d.rde_valor >= ur.ur_umbral2 THEN 2 WHEN d.rde_valor >= ur.ur_umbral1 THEN 1 ELSE 0 END as nivel_alerta')
            );

        $this->aplicarBusquedaTexto($consulta, [
            'ur.ur_nombre',
            'ur.ur_codigo',
            'ur.ur_rio',
            'ur.ur_zona_explotacion',
            'ur.ur_provincia',
            'ur.ur_tag_ip21',
            'ur.ur_tag_ip21_caudal',
            'ur.ur_tag_digital_ip21',
            'c.c_comunidad_autonoma',
        ], $tokens);

        $this->aplicarFiltroNivel($consulta, 'CASE WHEN d.rde_valor >= ur.ur_umbral3 THEN 3 WHEN d.rde_valor >= ur.ur_umbral2 THEN 2 WHEN d.rde_valor >= ur.ur_umbral1 THEN 1 ELSE 0 END', $filtros['nivel']);
        $this->aplicarFiltroActivo($consulta, 'ur.ur_activo', $filtros['solo_activos'], $filtros['solo_inactivos']);

        $this->aplicarPuntuacion($consulta, [
            'ur.ur_codigo' => 95,
            'ur.ur_nombre' => 70,
            'ur.ur_rio' => 45,
            'ur.ur_zona_explotacion' => 35,
            'ur.ur_tag_ip21' => 65,
            'ur.ur_tag_ip21_caudal' => 40,
            'c.c_comunidad_autonoma' => 40,
        ], $query, $tokens);

        return $consulta
            ->orderByDesc('score')
            ->orderByDesc('nivel_alerta')
            ->orderBy('ur.ur_nombre')
            ->limit(self::MAX_RESULTADOS_POR_BLOQUE)
            ->get();
    }

    private function buscarEpisodios(string $query, array $tokens, array $filtros)
    {
        $consulta = DB::table('umbrales_ranepisodio as re')
            ->leftJoin('umbrales_ccaa as c', 're.re_ccaa_id', '=', 'c.c_id')
            ->select(
                're.*',
                'c.c_comunidad_autonoma as nombre_ccaa',
                DB::raw("CASE WHEN re.re_hora_fin IS NULL THEN 'ACTIVO' ELSE 'HISTORICO' END as estado_episodio")
            );

        $this->aplicarBusquedaTexto($consulta, [
            're.re_nombre',
            're.re_id',
            'c.c_comunidad_autonoma',
            're.re_estaciones_activas',
            're.re_estaciones_historicas',
        ], $tokens);

        if ($filtros['solo_episodios_activos']) {
            $consulta->whereNull('re.re_hora_fin');
        }
        if ($filtros['solo_episodios_historicos']) {
            $consulta->whereNotNull('re.re_hora_fin');
        }

        $this->aplicarPuntuacion($consulta, [
            're.re_id' => 100,
            're.re_nombre' => 80,
            'c.c_comunidad_autonoma' => 45,
            're.re_estaciones_activas' => 30,
            're.re_estaciones_historicas' => 20,
        ], $query, $tokens);

        return $consulta
            ->orderByDesc('score')
            ->orderByDesc('re.re_hora_inicio')
            ->limit(self::MAX_RESULTADOS_POR_BLOQUE)
            ->get();
    }

    private function aplicarBusquedaTexto($consulta, array $campos, array $tokens): void
    {
        if (empty($tokens)) {
            return;
        }

        $consulta->where(function ($whereTokens) use ($campos, $tokens) {
            foreach ($tokens as $token) {
                $patron = $this->patronLike($token);
                $whereTokens->where(function ($whereCampos) use ($campos, $patron) {
                    foreach ($campos as $campo) {
                        $whereCampos->orWhereRaw("COALESCE({$campo}::text, '') ILIKE ? ESCAPE E'\\\\'", [$patron]);
                    }
                });
            }
        });
    }

    private function aplicarFiltroNivel($consulta, string $sqlNivel, ?int $nivel): void
    {
        if ($nivel === null) {
            return;
        }

        $consulta->whereRaw("({$sqlNivel}) = ?", [$nivel]);
    }

    private function aplicarFiltroActivo($consulta, string $campoActivo, bool $soloActivos, bool $soloInactivos): void
    {
        if ($soloActivos) {
            $consulta->where($campoActivo, 1);
        }
        if ($soloInactivos) {
            $consulta->where($campoActivo, 0);
        }
    }

    private function aplicarPuntuacion($consulta, array $camposConPeso, string $query, array $tokens): void
    {
        $partes = ['0'];
        $bindings = [];

        $patronGlobal = $this->patronLike($query);
        $patronPrefijo = $this->patronPrefijo($query);

        foreach ($camposConPeso as $campo => $peso) {
            $bonusToken = max(2, (int) floor($peso / 4));

            $partes[] = "CASE WHEN COALESCE({$campo}::text, '') ILIKE ? ESCAPE E'\\\\' THEN {$peso} ELSE 0 END";
            $bindings[] = $patronGlobal;

            $partes[] = "CASE WHEN COALESCE({$campo}::text, '') ILIKE ? ESCAPE E'\\\\' THEN " . ($peso + 12) . ' ELSE 0 END';
            $bindings[] = $patronPrefijo;

            foreach ($tokens as $token) {
                $partes[] = "CASE WHEN COALESCE({$campo}::text, '') ILIKE ? ESCAPE E'\\\\' THEN {$bonusToken} ELSE 0 END";
                $bindings[] = $this->patronLike($token);
            }
        }

        $consulta->selectRaw('(' . implode(' + ', $partes) . ') as score', $bindings);
    }

    private function extraerFiltros(string $query, array $tokens): array
    {
        $queryLower = Str::lower($query);
        $tipos = [];
        $mapaTipos = [
            'embalse' => 'embalse',
            'embalses' => 'embalse',
            'roea' => 'roea',
            'roeas' => 'roea',
            'marco' => 'marco',
            'marcos' => 'marco',
            'aforo' => 'aforo',
            'aforos' => 'aforo',
            'episodio' => 'episodio',
            'episodios' => 'episodio',
        ];

        foreach ($tokens as $token) {
            if (isset($mapaTipos[$token])) {
                $tipos[$mapaTipos[$token]] = true;
            }
        }

        $nivel = null;
        if (preg_match('/(?:^|\b)(?:a|alerta|nivel)\s*([123])(?:\b|$)/iu', $queryLower, $match)) {
            $nivel = (int) $match[1];
        } elseif (preg_match('/^\s*([123])\s*$/', $queryLower, $match)) {
            $nivel = (int) $match[1];
        }

        $soloActivos = $this->contienePalabra($queryLower, ['activo', 'activos', 'activa', 'activas'])
            && ! $this->contienePalabra($queryLower, ['inactivo', 'inactivos', 'inactiva', 'inactivas']);
        $soloInactivos = $this->contienePalabra($queryLower, ['inactivo', 'inactivos', 'inactiva', 'inactivas']);

        $soloEpisodiosActivos = $this->contieneAlguno($queryLower, ['episodios activos', 'episodio activo']);
        $soloEpisodiosHistoricos = $this->contienePalabra($queryLower, ['historico', 'historicos', 'cerrado', 'cerrados', 'finalizado', 'finalizados']);

        return [
            'tipos' => array_keys($tipos),
            'nivel' => $nivel,
            'solo_activos' => $soloActivos,
            'solo_inactivos' => $soloInactivos,
            'solo_episodios_activos' => $soloEpisodiosActivos,
            'solo_episodios_historicos' => $soloEpisodiosHistoricos,
        ];
    }

    private function debeBuscarTipo(string $tipo, array $tipos): bool
    {
        return empty($tipos) || in_array($tipo, $tipos, true);
    }

    private function extraerTokens(string $query): array
    {
        $partes = preg_split('/[\s,;:\/\\\\|]+/u', Str::lower($query)) ?: [];
        $tokens = [];

        foreach ($partes as $parte) {
            $limpio = trim($parte);
            if ($limpio === '') {
                continue;
            }

            if (! in_array($limpio, $tokens, true)) {
                $tokens[] = $limpio;
            }
        }

        return array_slice($tokens, 0, 10);
    }

    private function normalizarTexto(string $texto): string
    {
        return preg_replace('/\s+/u', ' ', trim($texto)) ?? '';
    }

    private function patronLike(string $texto): string
    {
        return '%' . $this->escaparLike($texto) . '%';
    }

    private function patronPrefijo(string $texto): string
    {
        return $this->escaparLike($texto) . '%';
    }

    private function escaparLike(string $texto): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $texto);
    }

    private function contieneAlguno(string $texto, array $fragmentos): bool
    {
        foreach ($fragmentos as $fragmento) {
            if (Str::contains($texto, $fragmento)) {
                return true;
            }
        }

        return false;
    }

    private function contienePalabra(string $texto, array $palabras): bool
    {
        foreach ($palabras as $palabra) {
            if (preg_match('/\b' . preg_quote($palabra, '/') . '\b/u', $texto)) {
                return true;
            }
        }

        return false;
    }

    private function respuestaVacia(): array
    {
        return [
            'embalses' => collect(),
            'roeas' => collect(),
            'marcos_control' => collect(),
            'aforos' => collect(),
            'episodios' => collect(),
            'meta' => [
                'query' => '',
                'total' => 0,
            ],
        ];
    }
}
