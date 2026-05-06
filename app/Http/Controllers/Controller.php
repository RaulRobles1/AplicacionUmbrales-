<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function esSuperusuario(): bool
    {
        return (bool) session('is_superuser', false);
    }

    protected function esStaff(): bool
    {
        return $this->esSuperusuario() || (bool) session('is_staff', false);
    }

    protected function autorizarSuperusuario(): void
    {
        abort_unless($this->esSuperusuario(), 403, 'No tienes permisos para realizar esta acción.');
    }

    protected function autorizarStaff(): void
    {
        abort_unless($this->esStaff(), 403, 'No tienes permisos para realizar esta acción.');
    }
}
