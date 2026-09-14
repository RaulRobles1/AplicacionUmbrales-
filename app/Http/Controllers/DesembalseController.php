<?php

namespace App\Http\Controllers;

use App\Models\EmbalsesRan;
use App\Models\EnvioDesembalse;
use App\Models\UmbralesRandireccionesenvio;
use App\Mail\PrevisionDesembalse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class DesembalseController extends Controller
{
    public function crear()
    {
        $this->comprobarSesion();

        $embalses = EmbalsesRan::query()
            ->where('er_activo', 1)
            ->orderBy('er_codigo')
            ->get(['er_codigo', 'er_nombre', 'er_comunidad_autonoma_id']);

        $destinatarios = UmbralesRandireccionesenvio::query()
            ->where('rde_activo', true)
            ->where('rde_tipo_envio', 'cco')
            ->orderBy('rde_nombre')
            ->get(['rde_direccion', 'rde_nombre', 'rde_ccaa']);

        return view('auth.desembalses_formulario', compact('embalses', 'destinatarios'));
    }

    public function guardar(Request $request)
    {

        $this->comprobarSesion();

        $request->validate([
            'embalse' => [
                'required',
                'string',
                Rule::exists('umbrales_embalsesran', 'er_codigo')->where('er_activo', 1),
            ],
            'caudal' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'fecha_hora_prevista' => ['required', 'date_format:Y-m-d\TH:i'],
        ]);

        $embalse = EmbalsesRan::query()
            ->where('er_codigo', $request->string('embalse')->toString())
            ->where('er_activo', 1)
            ->firstOrFail(['er_codigo', 'er_nombre', 'er_comunidad_autonoma_id']);

        $destinatarios = UmbralesRandireccionesenvio::query()
            ->where('rde_activo', true)
            ->where('rde_tipo_envio', 'cco')
            ->whereIn('rde_ccaa', [0, $embalse->er_comunidad_autonoma_id])
            ->pluck('rde_direccion')
            ->unique()
            ->values()
            ->all();

        if (config('mail.desembalses.test_mode')) {
            $direccionPrueba = config('mail.desembalses.test_address');

            if (!$direccionPrueba) {
                return back()
                    ->withInput()
                    ->withErrors(['email' => 'Configura MAIL_TEST_ADDRESS antes de enviar un correo de prueba.']);
            }

            $destinatarios = [$direccionPrueba];
        }

        $fechaPrevista = Carbon::createFromFormat('Y-m-d\TH:i', $request->input('fecha_hora_prevista'));

        Mail::to($destinatarios)->send(new PrevisionDesembalse(
            embalse: $embalse,
            caudal: $request->input('caudal'),
            fechaPrevista: $fechaPrevista,
        ));

        EnvioDesembalse::create([
            'codigo' => $request->string('embalse')->toString(),
            'caudal_a_desembolsar' => $request->input('caudal'),
            'fecha_hora_prevista' => $fechaPrevista,
            'fecha_envio' => now(),
            'usuario' => session('usuario'),
        ]);

        return redirect()->route('desembalses.crear')
            ->with('success', 'El envío de desembalse se ha guardado correctamente.');
    }

    private function comprobarSesion(): void
    {
        if (!session('id')) {
            redirect()->route('login')->send();
        }
    }
}
