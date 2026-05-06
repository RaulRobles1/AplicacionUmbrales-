<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AuthPermission
 *
 * @property int $id
 * @property string $name
 * @property int $content_type_id
 * @property string $codename
 * @property DjangoContentType $django_content_type
 * @property Collection|AuthUserUserPermission[] $auth_user_user_permissions
 * @property Collection|AuthGroupPermission[] $auth_group_permissions
 */
class AuthPermission extends Model
{
    protected $table = 'auth_permission';

    public $timestamps = false;

    protected $casts = [
        'content_type_id' => 'int',
    ];

    protected $fillable = [
        'name',
        'content_type_id',
        'codename',
    ];

    public function django_content_type()
    {
        return $this->belongsTo(DjangoContentType::class, 'content_type_id');
    }

    public function auth_user_user_permissions()
    {
        return $this->hasMany(AuthUserUserPermission::class, 'permission_id');
    }

    public function auth_group_permissions()
    {
        return $this->hasMany(AuthGroupPermission::class, 'permission_id');
    }
}
