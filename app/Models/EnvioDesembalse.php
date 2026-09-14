<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvioDesembalse extends Model
{
    protected $table = 'umbrales_enviodesembalses';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'caudal_a_desembolsar',
        'fecha_hora_prevista',
        'fecha_envio',
        'usuario',
    ];

    protected $casts = [
        'fecha_hora_prevista' => 'datetime',
        'fecha_envio' => 'datetime',
        'caudal_a_desembolsar' => 'decimal:2',
    ];
}
