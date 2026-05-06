<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmbralesProvincia extends Model
{
    protected $table = 'umbrales_provincias';

    protected $primaryKey = 'p_id';

    // Para que Laravel sepa que p_id no es autoincremental si lo llenamos a mano
    public $incrementing = true;
}
