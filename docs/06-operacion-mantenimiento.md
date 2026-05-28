# Operacion y mantenimiento

## Scheduler
- Programa `api:sync-datos` cada 5, 20, 35 y 50 minutos.
- En produccion:
  - `php artisan schedule:run` via cron (cada minuto), o
  - `php artisan schedule:work` en proceso continuo.

Referencias:
- [../routes/console.php](../routes/console.php)
- [../app/Console/Commands/SyncApiDatos.php](../app/Console/Commands/SyncApiDatos.php)

## Cola
- Config por defecto: `QUEUE_CONNECTION=database`.
- Workers recomendados:
  - `php artisan queue:listen --tries=1 --timeout=0`
  - o `php artisan queue:work`

Referencia:
- [../config/queue.php](../config/queue.php)

## Cache
- Cache de estado actual en base de datos si `CACHE_STORE=database`.
- Limpiar cache: `php artisan cache:clear`.
- Limpiar config: `php artisan config:clear`.

Referencias:
- [../config/cache.php](../config/cache.php)
- [../app/Services/EstadoActualService.php](../app/Services/EstadoActualService.php)

## Reverb y tiempo real (opcional)
- Si se usa Reverb, levantar el servidor websocket.
- Asegurar `BROADCAST_CONNECTION=reverb`.

Referencias:
- [../config/broadcasting.php](../config/broadcasting.php)
- [../config/reverb.php](../config/reverb.php)

## Logs
- Revisar logs de Laravel para errores de API o sincronizacion.

Referencia:
- [../config/logging.php](../config/logging.php)

## Backups
- Respaldar base de datos y el almacenamiento de PDFs de emergencias.
- Guardar variables de entorno en un repositorio seguro.

## Comandos utiles
- `api:sync-datos`: refresca cache de estado actual.
- `simular:Alertas {codigo} {nivel} {episodio?}`: inserta lectura simulada.
- `simular:crecida {codigo}`: genera serie de 24h para una estacion.

Referencias:
- [../app/Console/Commands/SimularAlertas.php](../app/Console/Commands/SimularAlertas.php)
- [../app/Console/Commands/SimularCrecida.php](../app/Console/Commands/SimularCrecida.php)

## Checklist de salud
- `api:sync-datos` actualiza `api_estado_actual_sync_at`.
- Cache contiene `api_estado_actual_global`.
- La API SAIH responde con valores recientes.
- El panel principal muestra estaciones y filtros funcionales.
- Reverb recarga vistas en tiempo real si esta habilitado.

## Troubleshooting rapido
- Panel sin datos: ejecutar `api:sync-datos` y revisar logs.
- Tendencias vacias: comprobar `API_SAIH_VALORES_URL`.
- No recarga en tiempo real: validar Reverb y variables `VITE_REVERB_*`.
- Errores de PDF: revisar tamaño de archivo y permisos del disco publico.

## Ver tambien
- [Indice](README.md)
- [Instalacion y entorno](02-instalacion-entorno.md)
