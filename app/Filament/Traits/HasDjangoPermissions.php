<?php

namespace App\Filament\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasDjangoPermissions
{
    protected static function getPermissionName(string $action): string
    {
        $model = static::getModel();

        $modelName = strtolower(class_basename($model));

        return "{$action}_{$modelName}";
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission(
            static::getPermissionName('view')
        ) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission(
            static::getPermissionName('add')
        ) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasPermission(
            static::getPermissionName('change')
        ) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasPermission(
            static::getPermissionName('delete')
        ) ?? false;
    }
}
