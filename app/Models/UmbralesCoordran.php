<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UmbralesCoordran
 *
 * @property string|null $lr_codigo_txt
 * @property string|null $lr_nombre
 * @property string|null $lr_nombre_corto
 * @property int|null $lr_utm_huso
 * @property string|null $lr_utm_x
 * @property string|null $lr_utm_y
 * @property int|null $lr_utm_z
 * @property string|null $latitud
 * @property string|null $longitud
 * @property bool|null $filtro
 * @property string|null $Column11
 * @property string|null $Column12
 * @property string|null $Column13
 */
class UmbralesCoordran extends Model
{
    protected $table = 'umbrales_coordran';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'lr_utm_huso' => 'int',
        'lr_utm_z' => 'int',
        'filtro' => 'bool',
    ];

    protected $fillable = [
        'lr_codigo_txt',
        'lr_nombre',
        'lr_nombre_corto',
        'lr_utm_huso',
        'lr_utm_x',
        'lr_utm_y',
        'lr_utm_z',
        'latitud',
        'longitud',
        'filtro',
        'Column11',
        'Column12',
        'Column13',
    ];
}
