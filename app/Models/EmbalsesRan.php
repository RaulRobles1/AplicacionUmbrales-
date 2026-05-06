<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmbalsesRan extends Model
{
    protected $table = 'umbrales_embalsesran';

    protected $primaryKey = 'er_codigo';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'er_codigo',
        'er_nombre',
        'er_provincia',
        'er_municipio',
        'er_rio',
        'er_capacidad',
        'er_umbral1',
        'er_umbral2',
        'er_umbral3',
        'er_tag_ip21',
        'er_tag_volumen',
        'er_tag_digital_ip21',
        'er_activo',
        'er_ultimo_nivel_alerta',
        'er_comunidad_autonoma_id',
        'er_ccaa_influencia',
        'er_titularidad',
    ];
}
