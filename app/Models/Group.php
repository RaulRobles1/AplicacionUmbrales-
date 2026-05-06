<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'auth_group';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'auth_group_permissions',
            'group_id',
            'permission_id'
        );
    }
}
