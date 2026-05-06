<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UmbralesRanmaximosaforo
 *
 * @property int $id
 * @property string $rma_estacion
 * @property float $rma_maximo_nivel
 * @property float $rma_maximo_caudal
 * @property string $rma_anyo_hidraulico
 */
class UmbralesRanmaximosaforo extends Model
{
    protected $table = 'umbrales_ranmaximosaforos';

    public $timestamps = false;

    protected $casts = [
        'rma_maximo_nivel' => 'float',
        'rma_maximo_caudal' => 'float',
    ];

    protected $fillable = [
        'rma_estacion',
        'rma_maximo_nivel',
        'rma_maximo_caudal',
        'rma_anyo_hidraulico',
    ];
}
