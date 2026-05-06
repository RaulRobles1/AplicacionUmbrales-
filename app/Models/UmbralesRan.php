<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmbralesRan extends Model
{
    protected $table = 'umbrales_umbralesran';

    protected $primaryKey = 'ur_codigo';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        're_id',
        're_hora_inicio',
        're_hora_fin',
        're_estaciones_activas',
        're_estaciones_historicas',
        're_boletines_generados',
        're_envio_pendiente_aforos',
        're_ccaa_id',
        're_usuario_cierre',
        're_comentario',
        're_estatus',
        're_nombre',
        're_envio_pendiente_embalses',
    ];
}
