<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UmbralesRandatosepisodio
 *
 * @property int $rde_id
 * @property string $rde_estacion
 * @property Carbon $rde_hora
 * @property float $rde_valor
 * @property float|null $rde_valor_accesorio
 * @property int $rde_ran_episodio_id
 * @property UmbralesRanepisodio $umbrales_ranepisodio
 */
class UmbralesRandatosepisodio extends Model
{
    protected $table = 'umbrales_randatosepisodio';

    protected $primaryKey = 'rde_id';

    public $timestamps = false;

    protected $casts = [
        'rde_hora' => 'datetime',
        'rde_valor' => 'float',
        'rde_valor_accesorio' => 'float',
        'rde_ran_episodio_id' => 'int',
    ];

    protected $fillable = [
        'rde_estacion',
        'rde_hora',
        'rde_valor',
        'rde_valor_accesorio',
        'rde_ran_episodio_id',
    ];

    public function umbrales_ranepisodio()
    {
        return $this->belongsTo(UmbralesRanepisodio::class, 'rde_ran_episodio_id');
    }
}
