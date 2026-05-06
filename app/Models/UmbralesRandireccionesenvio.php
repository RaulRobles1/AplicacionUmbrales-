<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UmbralesRandireccionesenvio
 *
 * @property int $rde_id
 * @property string $rde_direccion
 * @property int $rde_ccaa
 * @property string $rde_tipo_envio
 * @property bool $rde_activo
 * @property string|null $rde_nombre
 */
class UmbralesRandireccionesenvio extends Model
{
    protected $table = 'umbrales_randireccionesenvios';

    protected $primaryKey = 'rde_id';

    public $timestamps = false;

    protected $casts = [
        'rde_ccaa' => 'int',
        'rde_activo' => 'bool',
    ];

    protected $fillable = [
        'rde_direccion',
        'rde_ccaa',
        'rde_tipo_envio',
        'rde_activo',
        'rde_nombre',
    ];
}
