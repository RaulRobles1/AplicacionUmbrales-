# Instalacion y entorno

## Requisitos minimos
- PHP 8.2+ y Composer.
- Node.js LTS y npm.
- PostgreSQL recomendado (se usa `ILIKE` y `DATE_TRUNC`).
- Acceso a la API externa SAIH.

## Configuracion inicial (local)
1. composer install
2. copiar [../.env.example](../.env.example) a un archivo de entorno local
3. php artisan key:generate
4. php artisan migrate
5. npm install
6. npm run build

Alternativa: script definido en [../composer.json](../composer.json):
- composer run setup

Nota: el script setup ejecuta `migrate --force`. No usar en produccion sin revisar.

## Arranque en desarrollo
- composer run dev
- o manual:
  - php artisan serve
  - php artisan queue:listen --tries=1 --timeout=0
  - php artisan pail --timeout=0
  - npm run dev

## Base de datos y migraciones base
- Migraciones base disponibles:
  - [../database/migrations/0001_01_01_000000_create_users_table.php](../database/migrations/0001_01_01_000000_create_users_table.php)
  - [../database/migrations/0001_01_01_000001_create_cache_table.php](../database/migrations/0001_01_01_000001_create_cache_table.php)
  - [../database/migrations/0001_01_01_000002_create_jobs_table.php](../database/migrations/0001_01_01_000002_create_jobs_table.php)
- Las tablas de dominio (umbrales_* y auth_*) deben existir en la base de datos.

## Variables de entorno importantes
Archivo base: [../.env.example](../.env.example)

Variables propias (no estan en el archivo base y deben anadirse):
| Variable | Descripcion | Uso |
| --- | --- | --- |
| SOLO_EMERGENCIAS | true/false, filtra solo estaciones en alerta | [../app/Services/EstadoActualService.php](../app/Services/EstadoActualService.php) |
| ESTADO_ACTUAL_MAX_AGE_MIN | minutos de TTL para cache de estado actual | [../app/Services/EstadoActualService.php](../app/Services/EstadoActualService.php) |
| API_SAIH_URL | endpoint para valor actual por tag | [../app/Services/EstadoActualService.php](../app/Services/EstadoActualService.php) |
| API_SAIH_VALORES_URL | endpoint para valores en periodo (tendencia) | [../app/Services/EstadoActualService.php](../app/Services/EstadoActualService.php) |
| BROADCAST_CONNECTION | reverb, pusher, log, null | [../config/broadcasting.php](../config/broadcasting.php) |
| REVERB_APP_KEY | credencial backend Reverb | [../config/reverb.php](../config/reverb.php) |
| REVERB_APP_SECRET | credencial backend Reverb | [../config/reverb.php](../config/reverb.php) |
| REVERB_APP_ID | credencial backend Reverb | [../config/reverb.php](../config/reverb.php) |
| REVERB_HOST | host websocket Reverb | [../config/reverb.php](../config/reverb.php) |
| REVERB_PORT | puerto websocket Reverb | [../config/reverb.php](../config/reverb.php) |
| REVERB_SCHEME | http/https | [../config/reverb.php](../config/reverb.php) |
| VITE_REVERB_APP_KEY | clave para Reverb en frontend | [../resources/js/echo.js](../resources/js/echo.js) |
| VITE_REVERB_HOST | host websocket Reverb | [../resources/js/echo.js](../resources/js/echo.js) |
| VITE_REVERB_PORT | puerto websocket Reverb | [../resources/js/echo.js](../resources/js/echo.js) |
| VITE_REVERB_SCHEME | http/https | [../resources/js/echo.js](../resources/js/echo.js) |

Variables base (ya incluidas en el archivo de ejemplo):
- `APP_ENV`, `APP_DEBUG`, `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION`

## Servicios externos
- API SAIH: configurar `API_SAIH_URL` y `API_SAIH_VALORES_URL`.
- WebSockets (opcional): configurar Reverb y `BROADCAST_CONNECTION=reverb`.

## Storage publico
Para PDFs de emergencias se usa el disco publico. Ejecutar:
- php artisan storage:link

## Validacion rapida
- php artisan api:sync-datos
- Acceder al panel principal y comprobar fecha de sincronizacion.
- Ejecutar una busqueda y verificar resultados.

## Produccion
- Ajustar `APP_ENV=production` y `APP_DEBUG=false`.
- Optimizar cache del framework:
  - php artisan config:cache
  - php artisan route:cache
  - php artisan view:cache
- Configurar scheduler y workers de cola (ver [06 - Operacion y mantenimiento](06-operacion-mantenimiento.md)).

## Ver tambien
- [Indice](README.md)
- [Arquitectura y datos](03-arquitectura-datos.md)
