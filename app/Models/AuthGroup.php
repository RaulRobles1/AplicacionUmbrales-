<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AuthGroup
 *
 * @property int $id
 * @property string $name
 * @property Collection|AuthUserGroup[] $auth_user_groups
 * @property Collection|AuthGroupPermission[] $auth_group_permissions
 */
class AuthGroup extends Model
{
    protected $table = 'auth_group';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function auth_user_groups()
    {
        return $this->hasMany(AuthUserGroup::class, 'group_id');
    }

    public function auth_group_permissions()
    {
        return $this->hasMany(AuthGroupPermission::class, 'group_id');
    }
}
