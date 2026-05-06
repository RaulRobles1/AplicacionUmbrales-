<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SimularAlertas extends Command
{
    // 1. {codigo}: El ID de la estación (ej. AR18, E_32)
    // 2. {nivel}: El nivel de alerta  (0, 1, 2, 3) que queremos simular
    // 3. {episodio?}: (Opcional) El ID del episodio
    protected $signature = 'simular:Alertas {codigo} {nivel} {episodio?}';

    protected $description = 'Simula un dato real de sensor para una estación específica en un episodio activo';

    public function handle()
    {
        $codigo = $this->argument('codigo');
        $nivelObjetivo = (int) $this->argument('nivel');

        $idEpisodio = $this->argument('episodio') ?? DB::table('umbrales_ranepisodio')->max('re_id');

        if (! $idEpisodio) {
            return $this->error('No hay episodios en la base de datos.');
        }

        $esEmbalse = str_starts_with($codigo, 'E_');

        $tablaEst = $esEmbalse ? 'umbrales_embalsesran' : 'umbrales_umbralesran';
        $prefijo = $esEmbalse ? 'er_' : 'ur_';
        $colCod = $esEmbalse ? 'er_codigo' : 'ur_codigo';

        $estacion = DB::table($tablaEst)->where($colCod, $codigo)->first();
        if (! $estacion) {
            return $this->error("Estación no encontrada en la tabla $tablaEst.");
        }

        $u1 = (float) ($estacion->{$prefijo.'umbral1'} ?? 0);
        $u2 = (float) ($estacion->{$prefijo.'umbral2'} ?? 0);
        $u3 = (float) ($estacion->{$prefijo.'umbral3'} ?? 0);

        $ultimoDato = DB::table('umbrales_randatosepisodio')
            ->where('rde_estacion', $codigo)
            ->where('rde_ran_episodio_id', $idEpisodio)
            ->orderBy('rde_hora', 'desc')
            ->first();

        $nivelAnterior = 0;
        if ($ultimoDato) {
            $valAnterior = (float) $ultimoDato->rde_valor;
            if ($u3 > 0 && $valAnterior >= $u3) {
                $nivelAnterior = 3;
            } elseif ($u2 > 0 && $valAnterior >= $u2) {
                $nivelAnterior = 2;
            } elseif ($u1 > 0 && $valAnterior >= $u1) {
                $nivelAnterior = 1;
            }
        }

        // Dependiendo del nivel que pongamos en la terminal, genera un valor con sentido
        $nuevoValor = match ($nivelObjetivo) {
            3 => $u3 > 0 ? $u3 + 0.2 : 5.0,
            2 => $u2 > 0 ? ($u2 + $u3) / 2 : 3.0,
            1 => $u1 > 0 ? ($u1 + $u2) / 2 : 1.0,
            default => $u1 > 0 ? $u1 - 0.5 : 0.0,
        };
        if ($nuevoValor < 0) {
            $nuevoValor = 0;
        }

        $horaInsercion = Carbon::now()->addSeconds(1)->format('Y-m-d H:i:s');

        DB::table('umbrales_randatosepisodio')->insert([
            'rde_estacion' => $codigo,
            'rde_valor' => round($nuevoValor, 3),
            'rde_valor_accesorio' => null,
            'rde_hora' => $horaInsercion,
            'rde_ran_episodio_id' => $idEpisodio,
        ]);

        // guarda el ultimo nivel de alerta
        DB::table($tablaEst)->where($colCod, $codigo)->update([
            $prefijo.'ultimo_nivel_alerta' => $nivelAnterior,
        ]);

        $this->info("Valor simulado correctamente para la estación $codigo");
        $this->info('Nuevo Valor: '.round($nuevoValor, 3));
        $this->info("Episodio ID: $idEpisodio");
        $this->info("Hora: $horaInsercion");

        // Lanza un evento para actualizar las tablas
        event(new \App\Events\NuevoCambioRecibido);
    }
}
