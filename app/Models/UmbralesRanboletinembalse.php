<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UmbralesRanboletinembalse
 *
 * @property int $rbe_id
 * @property int $rbe_num_boletin
 * @property Carbon $rbe_hora
 * @property string $rbe_estaciones
 * @property int $rbe_ran_episodio_id
 * @property UmbralesRanepisodio $umbrales_ranepisodio
 */
class UmbralesRanboletinembalse extends Model
{
    protected $table = 'umbrales_ranboletinembalses';

    protected $primaryKey = 'rbe_id';

    public $timestamps = false;

    protected $casts = [
        'rbe_num_boletin' => 'int',
        'rbe_hora' => 'datetime',
        'rbe_ran_episodio_id' => 'int',
    ];

    protected $fillable = [
        'rbe_num_boletin',
        'rbe_hora',
        'rbe_estaciones',
        'rbe_ran_episodio_id',
    ];

    public function umbrales_ranepisodio()
    {
        return $this->belongsTo(UmbralesRanepisodio::class, 'rbe_ran_episodio_id');
    }
}
