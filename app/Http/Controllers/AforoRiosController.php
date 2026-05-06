<?php

/*


CODIGO QUE EN ESTE MOMENTO NO ESTA SIENDO UTILIZADO
ES LA LOGICA PARA EL DETALLE DE AFOROS EN RIOS



namespace App\Http\Controllers;

use App\Services\BuscadorService;

class AforoRiosController extends Controller
{
    protected $alertasService;

    public function __construct(BuscadorService $alertasService)
    {
        $this->alertasService = $alertasService;
    }

    // Muestra el detalle de los aforos en ríos filtrados por comunidad autónoma
    public function detalle($ccaa_id)
    {
        $todosAR = $this->alertasService->EstadoAforosRios();

        $aforosFiltrados = $todosAR->filter(function ($ar) use ($ccaa_id) {
            if (! $ar->ur_ccaa_influencia) {
                return false;
            }
            $ids = array_map('trim', explode(',', $ar->ur_ccaa_influencia));

            return in_array((string) $ccaa_id, $ids);
        });

        $nombresCCAA = $this->alertasService->getNombresCCAA();
        $nombreComunidad = $nombresCCAA[$ccaa_id] ?? 'Comunidad '.$ccaa_id;

        return view('auth.ar_detalle', [
            'aforos' => $aforosFiltrados,
            'titulo' => 'Aforo en Ríos - '.$nombreComunidad,
            'nombresCCAA' => $nombresCCAA,
        ]);
    }
}
