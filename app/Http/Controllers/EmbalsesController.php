<?php

/*


CODIGO QUE EN ESTE MOMENTO NO ESTA SIENDO UTILIZADO
ES LA LOGICA PARA EL DETALLE DE EMBALSES



namespace App\Http\Controllers;

use App\Services\BuscadorService;
use Illuminate\Http\Request;

class EmbalsesController extends Controller
{
    protected $alertasService;

    public function __construct(BuscadorService $alertasService)
    {
        $this->alertasService = $alertasService;
    }

    public function verCCAA($id)
    {
        $embalses = $this->alertasService->DetalleAlertasPorCCAA($id);

        $nombresCCAA = [
            2 => 'Aragón',
            8 => 'Castilla La Mancha',
            11 => 'Extremadura',
            13 => 'Madrid',
            7 => 'Castilla y León',
            99 => 'Portugal',
        ];

        $titulo = $nombresCCAA[$id] ?? "Comunidad Autónoma $id";

        return view('auth.embalses_detalle', [
            'embalses' => $embalses,
            'titulo' => $titulo,
        ]);
    }

    public function buscar(Request $request)
    {
        $query = $request->input('q');
        $resultados = $this->alertasService->BuscarGlobal($query);

        return view('auth.busqueda_resultados', [
            'embalses' => $resultados['embalses'],
            'roeas' => $resultados['roeas'],
            'marcos_control' => $resultados['marcos_control'],
            'aforos' => $resultados['aforos'],
            'titulo' => 'Resultados de la búsqueda: '.$query,
        ]);
    }
}
