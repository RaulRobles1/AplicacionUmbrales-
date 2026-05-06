<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// ASEGÚRATE DE QUE ESTA LÍNEA SEA EXACTAMENTE ASÍ:
use Illuminate\Database\Eloquent\Relations\HasMany;

class UmbralesCcaa extends Model
{
    protected $table = 'umbrales_ccaa';

    protected $primaryKey = 'c_id';

    public function provincias(): HasMany
    {
        return $this->hasMany(UmbralesProvincia::class, 'c_id', 'c_id');
    }

    public function getPlanEmergenciaAttribute()
    {
        $nombre = strtoupper($this->c_comunidad_autonoma);

        if (str_contains($nombre, 'MADRID')) {
            return 'INUNCAM';
        }
        if (str_contains($nombre, 'CASTILLA Y LE')) {
            return 'INUNcyl';
        }
        if (str_contains($nombre, 'MANCHA')) {
            return 'PRICAM';
        }
        if (str_contains($nombre, 'EXTREMADURA')) {
            return 'INUNCAEX';
        }

        return null;
    }
}
