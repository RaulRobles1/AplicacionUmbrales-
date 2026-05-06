<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GraficoController extends Controller
{
    public function verGrafico($codigo)
    {
        return view('auth.grafico', compact('codigo'));
    }

    public function obtenerHistorial($codigo, Request $request)
    {
        $codigo = trim((string) $codigo);
        $codigoNormalizado = strtoupper($codigo);
        $episodioId = $request->integer('episodio_id');

        $rangoSolicitado = $request->query('dias', '7');
        $rangoPermitido = ['1', '7', '10', '30', '365', 'all'];
        $dias = in_array((string) $rangoSolicitado, $rangoPermitido, true)
            ? (string) $rangoSolicitado
            : '7';

        $ultimaFechaObj = DB::table('umbrales_randatosepisodio')
            ->whereRaw('UPPER(TRIM(rde_estacion)) = ?', [$codigoNormalizado])
            ->whereNotNull('rde_hora')
            ->when($episodioId > 0, function ($query) use ($episodioId) {
                $query->where('rde_ran_episodio_id', $episodioId);
            })
            ->orderBy('rde_hora', 'desc')
            ->first(['rde_hora']);

        if (! $ultimaFechaObj) {
            return response()->json(['fechas' => [], 'valores' => [], 'umbrales' => [], 'maximo_historico' => null]);
        }

        $query = DB::table('umbrales_randatosepisodio')
            ->whereRaw('UPPER(TRIM(rde_estacion)) = ?', [$codigoNormalizado])
            ->whereNotNull('rde_hora')
            ->whereNotNull('rde_valor')
            ->when($episodioId > 0, function ($query) use ($episodioId) {
                $query->where('rde_ran_episodio_id', $episodioId);
            });

        if ($dias !== 'all') {
            $fechaInicio = Carbon::parse($ultimaFechaObj->rde_hora)->subDays((int) $dias);
            $query->where('rde_hora', '>=', $fechaInicio);
        }

        if ($dias === 'all' || $dias === '30') {
            $historial = $query->select(
                DB::raw("DATE_TRUNC('hour', rde_hora) as fecha_agrupada"),
                DB::raw('ROUND(AVG(rde_valor)::numeric, 2) as media_valor')
            )
                ->groupBy('fecha_agrupada')
                ->orderBy('fecha_agrupada', 'asc')
                ->get();
        } else {
            $historial = $query->select(
                'rde_hora as fecha_agrupada',
                'rde_valor as media_valor'
            )
                ->orderBy('rde_hora', 'asc')
                ->get();
        }

        $fechas = [];
        $valores = [];

        foreach ($historial as $dato) {
            $fechas[] = Carbon::parse($dato->fecha_agrupada)->timestamp * 1000;
            $valores[] = (float) $dato->media_valor;
        }

        $esEmbalse = str_starts_with($codigoNormalizado, 'E_');
        $prefijo = $esEmbalse ? 'er_' : 'ur_';

        $tablaUmbrales = $esEmbalse ? 'umbrales_embalsesran' : 'umbrales_umbralesran';
        $columnaCodigo = $esEmbalse ? 'er_codigo' : 'ur_codigo';

        $datosUmbral = DB::table($tablaUmbrales)
            ->whereRaw('UPPER(TRIM('.$columnaCodigo.')) = ?', [$codigoNormalizado])
            ->first();

        $umbralesExtraidos = [];
        $maximoHistorico = null;

        if ($datosUmbral) {
            $u1 = $prefijo.'umbral1';
            $u2 = $prefijo.'umbral2';
            $u3 = $prefijo.'umbral3';

            if (isset($datosUmbral->$u1) && $datosUmbral->$u1 > 0) {
                $umbralesExtraidos[] = ['valor' => (float) $datosUmbral->$u1, 'color' => '#facc15', 'texto' => 'Alerta 1'];
            }
            if (isset($datosUmbral->$u2) && $datosUmbral->$u2 > 0) {
                $umbralesExtraidos[] = ['valor' => (float) $datosUmbral->$u2, 'color' => '#fb923c', 'texto' => 'Alerta 2'];
            }
            if (isset($datosUmbral->$u3) && $datosUmbral->$u3 > 0) {
                $umbralesExtraidos[] = ['valor' => (float) $datosUmbral->$u3, 'color' => '#ef4444', 'texto' => 'Alerta 3'];
            }
        }

        // Solo aplica a aforos: máximo histórico por código de estación (AR01, AR03, ...)
        if (! $esEmbalse) {
            $maximoHistoricoDb = DB::table('umbrales_ranmaximosaforos')
                ->whereRaw('UPPER(TRIM(rma_estacion)) = UPPER(TRIM(?))', [$codigo])
                ->max('rma_maximo_nivel');

            if (is_numeric($maximoHistoricoDb)) {
                $maximoHistorico = (float) $maximoHistoricoDb;
            }
        }

        return response()->json([
            'fechas' => $fechas,
            'valores' => $valores,
            'umbrales' => $umbralesExtraidos,
            'maximo_historico' => $maximoHistorico,
        ]);
    }
}
