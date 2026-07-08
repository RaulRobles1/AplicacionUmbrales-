# Vision general

## Proposito del sistema
Umbrales es una aplicacion Laravel para monitorizar estaciones hidrologicas, gestionar episodios y registrar situaciones de emergencia. Combina datos de base de datos propia con lecturas en tiempo real de una API externa (SAIH) y presenta paneles, mapas y graficos para analisis rapido.

## Alcance funcional
- Monitorizacion en tiempo real con umbrales y niveles de alerta.
- Gestion de episodios (activos e historicos) y detalle por estaciones.
- Registro de emergencias con PDF adjunto o generado.
- Busqueda global por estaciones y episodios.
- Administracion de tablas maestras desde Filament.
- Actualizacion automatica mediante scheduler y eventos en tiempo real.

## Roles y permisos
- El control de acceso se basa en sesiones y validaciones internas.
- Superusuario: gestiona usuarios y tareas sensibles.
- Staff: puede cerrar o renombrar episodios y registrar emergencias.
- Usuario: acceso general de lectura.

Referencia de control de acceso: [../app/Http/Controllers/Controller.php](../app/Http/Controllers/Controller.php).

## Entradas y salidas
### Entradas
- Base de datos con tablas umbrales_* y auth_*.
- API externa SAIH para valores actuales y tendencias.
- Formularios de emergencia y gestion de usuarios.

### Salidas
- Panel de estado actual con filtros.
- Listados y detalle de episodios.
- Mapa de estaciones con clustering.
- Graficos historicos y mini graficos.
- PDFs de emergencias.
- Recargas automaticas por eventos en tiempo real.

## Modulos principales
- Estado actual: cachea y presenta estaciones en alerta. Ver [03 - Arquitectura y datos](03-arquitectura-datos.md).
- Episodios: listados y detalle de estaciones. Ver [04 - Funcionalidades y rutas](04-funcionalidades-rutas.md).
- Emergencias: registro, PDF y plan mensual. Ver [04 - Funcionalidades y rutas](04-funcionalidades-rutas.md).
- Buscador: filtrado y ranking de resultados. Ver [03 - Arquitectura y datos](03-arquitectura-datos.md).
- Admin Filament: mantenimiento de tablas maestras. Ver [05 - Admin Filament](05-admin-filament.md).
- Tiempo real: recarga por eventos. Ver [07 - Frontend y tiempo real](07-frontend-y-tiempo-real.md).

## Pantallas principales (referencias)
- Panel principal: [../resources/views/auth/inicio_umbrales.blade.php](../resources/views/auth/inicio_umbrales.blade.php)
- Estado actual por CCAA: [../resources/views/auth/vista_estadoActual.blade.php](../resources/views/auth/vista_estadoActual.blade.php)
- Listado de episodios: [../resources/views/auth/episodios_lista.blade.php](../resources/views/auth/episodios_lista.blade.php)
- Detalle de episodio: [../resources/views/auth/episodios_detalle.blade.php](../resources/views/auth/episodios_detalle.blade.php)
- Mapa global: [../resources/views/auth/mapa_global.blade.php](../resources/views/auth/mapa_global.blade.php)
- Graficos: [../resources/views/auth/grafico.blade.php](../resources/views/auth/grafico.blade.php)
- Emergencias: [../resources/views/auth/situacion_formulario.blade.php](../resources/views/auth/situacion_formulario.blade.php)
- Plan de emergencia: [../resources/views/auth/vista_PlanEmergencia.blade.php](../resources/views/auth/vista_PlanEmergencia.blade.php)

## Integraciones externas
- API SAIH: configurada por `API_SAIH_URL` y `API_SAIH_VALORES_URL`.
  - Implementacion: [../app/Services/EstadoActualService.php](../app/Services/EstadoActualService.php)
- Reverb y Echo para tiempo real.
  - Configuracion frontend: [../resources/js/echo.js](../resources/js/echo.js)
  - Configuracion backend: [../config/reverb.php](../config/reverb.php)

## Supuestos y decisiones
- Motor de base de datos esperado: PostgreSQL (uso de `ILIKE` y `DATE_TRUNC`).
- Cache para estado actual y filtrado por CCAA con TTL.
- Sesiones persistidas en archivo si `SESSION_DRIVER=file`.

## Ver tambien
- [Indice](README.md)
- [Arquitectura y datos](03-arquitectura-datos.md)
- [Operacion y mantenimiento](06-operacion-mantenimiento.md)
