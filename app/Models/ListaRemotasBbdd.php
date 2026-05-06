<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ListaRemotasBbdd
 *
 * @property string|null $LR_CODIGO_TXT
 * @property string|null $LR_NOMBRE
 * @property string|null $LR_NOMBRE_CORTO
 * @property int|null $LR_UTM_HUSO
 * @property string|null $LR_UTM_X
 * @property string|null $LR_UTM_Y
 * @property int|null $LR_UTM_Z
 * @property string|null $LATITUD
 * @property string|null $LONGITUD
 * @property bool|null $FILTRO
 */
class ListaRemotasBbdd extends Model
{
    protected $table = 'lista_remotas_bbdd';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'LR_UTM_HUSO' => 'int',
        'LR_UTM_Z' => 'int',
        'FILTRO' => 'bool',
    ];

    protected $fillable = [
        'LR_CODIGO_TXT',
        'LR_NOMBRE',
        'LR_NOMBRE_CORTO',
        'LR_UTM_HUSO',
        'LR_UTM_X',
        'LR_UTM_Y',
        'LR_UTM_Z',
        'LATITUD',
        'LONGITUD',
        'FILTRO',
    ];
}
