<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AuthUserGroup
 *
 * @property int $id
 * @property int $user_id
 * @property int $group_id
 * @property AuthUser $auth_user
 * @property AuthGroup $auth_group
 */
class AuthUserGroup extends Model
{
    protected $table = 'auth_user_groups';

    public $timestamps = false;

    protected $casts = [
        'user_id' => 'int',
        'group_id' => 'int',
    ];

    protected $fillable = [
        'user_id',
        'group_id',
    ];

    public function auth_user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }

    public function auth_group()
    {
        return $this->belongsTo(AuthGroup::class, 'group_id');
    }
}
