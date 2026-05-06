<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'auth_permission';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'codename',
        'content_type_id',
    ];
}
