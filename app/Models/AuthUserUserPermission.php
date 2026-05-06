<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AuthUserUserPermission
 *
 * @property int $id
 * @property int $user_id
 * @property int $permission_id
 * @property AuthUser $auth_user
 * @property AuthPermission $auth_permission
 */
class AuthUserUserPermission extends Model
{
    protected $table = 'auth_user_user_permissions';

    public $timestamps = false;

    protected $casts = [
        'user_id' => 'int',
        'permission_id' => 'int',
    ];

    protected $fillable = [
        'user_id',
        'permission_id',
    ];

    public function auth_user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }

    public function auth_permission()
    {
        return $this->belongsTo(AuthPermission::class, 'permission_id');
    }
}
