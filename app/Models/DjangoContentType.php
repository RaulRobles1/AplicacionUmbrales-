<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DjangoContentType
 *
 * @property int $id
 * @property string $app_label
 * @property string $model
 * @property Collection|DjangoAdminLog[] $django_admin_logs
 * @property Collection|AuthPermission[] $auth_permissions
 */
class DjangoContentType extends Model
{
    protected $table = 'django_content_type';

    public $timestamps = false;

    protected $fillable = [
        'app_label',
        'model',
    ];

    public function django_admin_logs()
    {
        return $this->hasMany(DjangoAdminLog::class, 'content_type_id');
    }

    public function auth_permissions()
    {
        return $this->hasMany(AuthPermission::class, 'content_type_id');
    }
}
