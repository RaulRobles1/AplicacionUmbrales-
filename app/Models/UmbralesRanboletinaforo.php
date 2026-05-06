<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UmbralesRanboletinaforo
 *
 * @property int $rba_id
 * @property int $rba_num_boletin
 * @property Carbon $rba_hora
 * @property string $rba_estaciones
 * @property int $rba_ran_episodio_id
 * @property UmbralesRanepisodio $umbrales_ranepisodio
 */
class UmbralesRanboletinaforo extends Model
{
    protected $table = 'umbrales_ranboletinaforos';

    protected $primaryKey = 'rba_id';

    public $timestamps = false;

    protected $casts = [
        'rba_num_boletin' => 'int',
        'rba_hora' => 'datetime',
        'rba_ran_episodio_id' => 'int',
    ];

    protected $fillable = [
        'rba_num_boletin',
        'rba_hora',
        'rba_estaciones',
        'rba_ran_episodio_id',
    ];

    public function umbrales_ranepisodio()
    {
        return $this->belongsTo(UmbralesRanepisodio::class, 'rba_ran_episodio_id');
    }
}
