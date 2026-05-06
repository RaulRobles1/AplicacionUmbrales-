<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AuthGroupPermission
 *
 * @property int $id
 * @property int $group_id
 * @property int $permission_id
 * @property AuthGroup $auth_group
 * @property AuthPermission $auth_permission
 */
class AuthGroupPermission extends Model
{
    protected $table = 'auth_group_permissions';

    public $timestamps = false;

    protected $casts = [
        'group_id' => 'int',
        'permission_id' => 'int',
    ];

    protected $fillable = [
        'group_id',
        'permission_id',
    ];

    public function auth_group()
    {
        return $this->belongsTo(AuthGroup::class, 'group_id');
    }

    public function auth_permission()
    {
        return $this->belongsTo(AuthPermission::class, 'permission_id');
    }
}
