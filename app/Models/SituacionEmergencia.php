<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SituacionEmergencia extends Model
{
    use HasFactory;

    protected $table = 'situacion_emergencia';

    protected $fillable = [
        'ccaa_id',
        'provincia_id',
        'nivel',
        'fecha',
        'hora',
        'descripcion',
        'ruta_pdf',
        'usuario_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function ccaa()
    {
        return $this->belongsTo(UmbralesCcaa::class, 'ccaa_id', 'c_id');
    }
}
