<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AuthUser
 *
 * @property int $id
 * @property string $password
 * @property Carbon|null $last_login
 * @property bool $is_superuser
 * @property string $username
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property bool $is_staff
 * @property bool $is_active
 * @property Carbon $date_joined
 * @property Collection|AuthUserGroup[] $auth_user_groups
 * @property Collection|AuthUserUserPermission[] $auth_user_user_permissions
 * @property Collection|DjangoAdminLog[] $django_admin_logs
 */
class AuthUser extends Model
{
    protected $table = 'auth_user';

    public $timestamps = false;

    protected $casts = [
        'last_login' => 'datetime',
        'is_superuser' => 'bool',
        'is_staff' => 'bool',
        'is_active' => 'bool',
        'date_joined' => 'datetime',
    ];

    protected $hidden = [
        'password',
    ];

    protected $fillable = [
        'password',
        'last_login',
        'is_superuser',
        'username',
        'first_name',
        'last_name',
        'email',
        'is_staff',
        'is_active',
        'date_joined',
    ];

    public function auth_user_groups()
    {
        return $this->hasMany(AuthUserGroup::class, 'user_id');
    }

    public function auth_user_user_permissions()
    {
        return $this->hasMany(AuthUserUserPermission::class, 'user_id');
    }

    public function django_admin_logs()
    {
        return $this->hasMany(DjangoAdminLog::class, 'user_id');
    }
}
