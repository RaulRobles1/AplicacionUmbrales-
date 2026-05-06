<?php

namespace App\Console\Commands;

use App\Services\EstadoActualService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncApiDatos extends Command
{
    protected $signature = 'api:sync-datos';

    protected $description = 'Sincroniza datos de API externa y actualiza la cache de estado actual';

    public function __construct(private readonly EstadoActualService $estadoActualSyncService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $lock = Cache::lock('api:sync-datos:lock', 600);

        if (! $lock->get()) {
            $this->warn('Sincronizacion en curso. Se omite esta ejecucion.');

            return self::SUCCESS;
        }

        try {
            $this->estadoActualSyncService->sincronizarCaches();
            $this->info('Sincronizacion completada.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            Log::error('Fallo al sincronizar datos de API externa', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Fallo en sincronizacion. Revisar logs.');

            return self::FAILURE;
        } finally {
            optional($lock)->release();
        }
    }
}
