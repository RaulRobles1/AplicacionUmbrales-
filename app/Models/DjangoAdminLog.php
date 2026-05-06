<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DjangoAdminLog
 *
 * @property int $id
 * @property Carbon $action_time
 * @property string|null $object_id
 * @property string $object_repr
 * @property int $action_flag
 * @property string $change_message
 * @property int|null $content_type_id
 * @property int $user_id
 * @property DjangoContentType|null $django_content_type
 * @property AuthUser $auth_user
 */
class DjangoAdminLog extends Model
{
    protected $table = 'django_admin_log';

    public $timestamps = false;

    protected $casts = [
        'action_time' => 'datetime',
        'action_flag' => 'int',
        'content_type_id' => 'int',
        'user_id' => 'int',
    ];

    protected $fillable = [
        'action_time',
        'object_id',
        'object_repr',
        'action_flag',
        'change_message',
        'content_type_id',
        'user_id',
    ];

    public function django_content_type()
    {
        return $this->belongsTo(DjangoContentType::class, 'content_type_id');
    }

    public function auth_user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }
}
