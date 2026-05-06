<?php

/*

CODIGO QUE EN ESTE MOMENTO NO ESTA SIENDO UTILIZADO
ES LA LOGICA PARA EL DETALLE DE MARCOS DE CONTROL



namespace App\Http\Controllers;

use App\Services\BuscadorService;

class MarcoControlController extends Controller
{
    protected $alertasService;

    public function __construct(BuscadorService $alertasService)
    {
        $this->alertasService = $alertasService;
    }

    public function detalle($zona)
    {
        $todosLosMC = $this->alertasService->EstadoMarcosControl();

        if ($zona === 'general') {
            $marcosControl = $todosLosMC;
            $titulo = 'Marcos de Control - Total General';
        } else {
            $marcosControl = $todosLosMC->where('ur_zona_explotacion', 'ILIKE', "%$zona%");
            $titulo = 'Marcos de Control - '.ucfirst($zona);
        }

        $nombresCCAA = $this->alertasService->getNombresCCAA();

        return view('auth.mc_detalle', [
            'roeas' => $marcosControl,
            'titulo' => $titulo,
            'nombresCCAA' => $nombresCCAA,
        ]);
    }
}
