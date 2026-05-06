<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UmbralesRanepisodio
 *
 * @property int $re_id
 * @property Carbon $re_hora_inicio
 * @property Carbon|null $re_hora_fin
 * @property string $re_estaciones_activas
 * @property string $re_estaciones_historicas
 * @property int $re_boletines_generados
 * @property bool $re_envio_pendiente_aforos
 * @property int $re_ccaa_id
 * @property string|null $re_usuario_cierre
 * @property string|null $re_comentario
 * @property string|null $re_estatus
 * @property string|null $re_nombre
 * @property bool $re_envio_pendiente_embalses
 * @property UmbralesCcaa $umbrales_ccaa
 * @property Collection|UmbralesRanboletinaforo[] $umbrales_ranboletinaforos
 * @property Collection|UmbralesRanboletinembalse[] $umbrales_ranboletinembalses
 * @property Collection|UmbralesRandatosepisodio[] $umbrales_randatosepisodios
 */
class UmbralesRanepisodio extends Model
{
    protected $table = 'umbrales_ranepisodio';

    protected $primaryKey = 're_id';

    public $timestamps = false;

    protected $casts = [
        're_hora_inicio' => 'datetime',
        're_hora_fin' => 'datetime',
        're_boletines_generados' => 'int',
        're_envio_pendiente_aforos' => 'bool',
        're_ccaa_id' => 'int',
        're_envio_pendiente_embalses' => 'bool',
    ];

    protected $fillable = [
        're_hora_inicio',
        're_hora_fin',
        're_estaciones_activas',
        're_estaciones_historicas',
        're_boletines_generados',
        're_envio_pendiente_aforos',
        're_ccaa_id',
        're_usuario_cierre',
        're_comentario',
        're_estatus',
        're_nombre',
        're_envio_pendiente_embalses',
    ];

    public function umbrales_ccaa()
    {
        return $this->belongsTo(UmbralesCcaa::class, 're_ccaa_id');
    }

    public function umbrales_ranboletinaforos()
    {
        return $this->hasMany(UmbralesRanboletinaforo::class, 'rba_ran_episodio_id');
    }

    public function umbrales_ranboletinembalses()
    {
        return $this->hasMany(UmbralesRanboletinembalse::class, 'rbe_ran_episodio_id');
    }

    public function umbrales_randatosepisodios()
    {
        return $this->hasMany(UmbralesRandatosepisodio::class, 'rde_ran_episodio_id');
    }
}
