<?php

/*


CODIGO QUE EN ESTE MOMENTO NO ESTA SIENDO UTILIZADO
ES LA LOGICA PARA EL DETALLE DE LAS ROEAS



namespace App\Http\Controllers;

use App\Services\BuscadorService;

class RoeasController extends Controller
{
    protected $alertasService;

    public function __construct(BuscadorService $alertasService)
    {
        $this->alertasService = $alertasService;
    }

    public function mostrarDetalle()
    {
        $roeas = $this->alertasService->EstadoRoeas();
        $resumenAlertas = $this->alertasService->ResumenAlertas();
        $totalGlobal = $this->alertasService->TotalAlertasGlobales();
        $nombresCCAA = $this->alertasService->getNombresCCAA();

        return view('auth.roeas_detalle', [
            'roeas' => $roeas,
            'resumenAlertas' => $resumenAlertas,
            'totalGlobal' => $totalGlobal,
            'nombresCCAA' => $nombresCCAA,
            'titulo' => 'Vista General de ROEAS',
        ]);
    }
}
