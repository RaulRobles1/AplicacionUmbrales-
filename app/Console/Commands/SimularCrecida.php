<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SimularCrecida extends Command
{
    protected $signature = 'simular:crecida {codigo}';

    protected $description = 'Simula una crecida de 24 horas para una estación asociada a un episodio';

    public function handle()
    {
        $codigo = $this->argument('codigo');

        // Coge los ultimos episodios activos
        $episodio = DB::table('umbrales_ranepisodio')
            ->orderByRaw('re_hora_fin IS NULL DESC')
            ->orderBy('re_hora_inicio', 'desc')
            ->first();

        if (! $episodio) {
            $this->error('No hay ningún episodio activo actualmente');

            return;
        }

        $this->info("Se va a simular: $codigo ID: {$episodio->re_id}");

        // Diferenciar entre embalse o lo demas por el prefijo
        $esEmbalse = str_starts_with($codigo, 'E_');
        $tabla = $esEmbalse ? 'umbrales_embalsesran' : 'umbrales_umbralesran';
        $prefijo = $esEmbalse ? 'er_' : 'ur_';
        $colCodigo = $esEmbalse ? 'er_codigo' : 'ur_codigo';

        $estacion = DB::table($tabla)->where($colCodigo, $codigo)->first();

        if (! $estacion) {
            $this->error("La estación $codigo no existe en la tabla $tabla.");

            return;
        }

        $u1 = (float) $estacion->{$prefijo.'umbral1'};
        $u2 = (float) $estacion->{$prefijo.'umbral2'};
        $u3 = (float) $estacion->{$prefijo.'umbral3'};

        // genera cada 15 min un punto para una crecida y una bajada durante 24horas
        $ahora = now();
        $registros = [];

        for ($i = 0; $i <= 96; $i++) {
            $horaPunto = $ahora->copy()->subMinutes((96 - $i) * 15);

            $progreso = $i / 96;
            $exponente = -pow(($progreso - 0.6), 2) / 0.05;
            $curva = exp($exponente);

            $valorBase = $u1 * 0.8;
            $rangoValores = ($u3 * 1.3) - $valorBase;
            $valorFinal = $valorBase + ($curva * $rangoValores);

            $registros[] = [
                'rde_estacion' => $codigo,
                'rde_valor' => round($valorFinal, 2),
                'rde_hora' => $horaPunto,
                'rde_ran_episodio_id' => $episodio->re_id,
            ];
        }

        // Insertar los datos simulados en la tabla
        DB::table('umbrales_randatosepisodio')->insert($registros);

        DB::table('umbrales_randatosepisodio')->insert($registros);

        $this->info("¡Simulación completada con éxito para $codigo!");

        // Evento para recargar las tablas automaticamente
        event(new \App\Events\NuevoCambioRecibido);
        $this->info("Simulación completada en: $codigo!");
    }
}
