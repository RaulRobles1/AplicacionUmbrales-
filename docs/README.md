# Documentacion tecnica - Umbrales

Manual de mantenimiento y operacion del sistema. Ultima revision: 2026-05-22.

## Como usar este manual
- Para instalar o desplegar: [02 - Instalacion y entorno](02-instalacion-entorno.md) y [06 - Operacion y mantenimiento](06-operacion-mantenimiento.md).
- Para cambios de logica: [01 - Vision general](01-vision-general.md), [03 - Arquitectura y datos](03-arquitectura-datos.md) y [04 - Funcionalidades y rutas](04-funcionalidades-rutas.md).
- Para administrar datos maestros: [05 - Admin Filament](05-admin-filament.md).
- Para UI y tiempo real: [07 - Frontend y tiempo real](07-frontend-y-tiempo-real.md).

## Indice
- [01 - Vision general](01-vision-general.md)
- [02 - Instalacion y entorno](02-instalacion-entorno.md)
- [03 - Arquitectura y datos](03-arquitectura-datos.md)
- [04 - Funcionalidades y rutas](04-funcionalidades-rutas.md)
- [05 - Admin Filament](05-admin-filament.md)
- [06 - Operacion y mantenimiento](06-operacion-mantenimiento.md)
- [07 - Frontend y tiempo real](07-frontend-y-tiempo-real.md)

## Mapa rapido del repositorio
- carpeta app: controladores, servicios, modelos, eventos, comandos
- carpeta routes: rutas web y scheduler
- carpeta config: configuracion de Laravel y servicios
- carpeta resources: vistas Blade y assets
- carpeta database: migraciones base
- carpeta public: assets build y recursos estaticos
- carpeta storage: logs, cache, archivos generados
- carpeta tests: pruebas

## Pantallas y flujos clave
- Inicio y panel principal: [../resources/views/auth/inicio_umbrales.blade.php](../resources/views/auth/inicio_umbrales.blade.php)
- Estado actual por CCAA: [../resources/views/auth/vista_estadoActual.blade.php](../resources/views/auth/vista_estadoActual.blade.php)
- Listado de episodios: [../resources/views/auth/episodios_lista.blade.php](../resources/views/auth/episodios_lista.blade.php)
- Detalle de episodio: [../resources/views/auth/episodios_detalle.blade.php](../resources/views/auth/episodios_detalle.blade.php)
- Graficos de estaciones: [../resources/views/auth/grafico.blade.php](../resources/views/auth/grafico.blade.php)
- Busqueda global: [../resources/views/auth/busqueda_resultados.blade.php](../resources/views/auth/busqueda_resultados.blade.php)
- Mapa global: [../resources/views/auth/mapa_global.blade.php](../resources/views/auth/mapa_global.blade.php)
- Emergencias (formulario): [../resources/views/auth/situacion_formulario.blade.php](../resources/views/auth/situacion_formulario.blade.php)
- Plan de emergencia: [../resources/views/auth/vista_PlanEmergencia.blade.php](../resources/views/auth/vista_PlanEmergencia.blade.php)
- Gestion de usuarios: [../resources/views/auth/confUsuarios.blade.php](../resources/views/auth/confUsuarios.blade.php)
- Edicion de usuario: [../resources/views/auth/editarUsuario.blade.php](../resources/views/auth/editarUsuario.blade.php)
- Login: [../resources/views/auth/login_umbrales.blade.php](../resources/views/auth/login_umbrales.blade.php)
- Plantilla base y navegacion: [../resources/views/auth/plantilla.blade.php](../resources/views/auth/plantilla.blade.php)

## Referencias rapidas de codigo
- [../routes/web.php](../routes/web.php)
- [../routes/console.php](../routes/console.php)
- [../app/Services/EstadoActualService.php](../app/Services/EstadoActualService.php)
- [../app/Services/BuscadorService.php](../app/Services/BuscadorService.php)
- [../app/Http/Controllers/EmergenciaController.php](../app/Http/Controllers/EmergenciaController.php)
- [../app/Http/Controllers/EpisodioController.php](../app/Http/Controllers/EpisodioController.php)
- [../app/Http/Controllers/GraficoController.php](../app/Http/Controllers/GraficoController.php)
- [../app/Http/Controllers/UserController.php](../app/Http/Controllers/UserController.php)
- [../app/Events/NuevoCambioRecibido.php](../app/Events/NuevoCambioRecibido.php)
- [../app/Console/Commands/SyncApiDatos.php](../app/Console/Commands/SyncApiDatos.php)
- [../app/Console/Commands/SimularAlertas.php](../app/Console/Commands/SimularAlertas.php)
- [../app/Console/Commands/SimularCrecida.php](../app/Console/Commands/SimularCrecida.php)
- [../config/database.php](../config/database.php)
- [../config/queue.php](../config/queue.php)
- [../config/broadcasting.php](../config/broadcasting.php)
- [../config/reverb.php](../config/reverb.php)

## Actualizacion del manual
- Actualiza la fecha de revision y anota cambios funcionales relevantes.
- Mantiene los enlaces alineados con el codigo real.
- Si se agrega un modulo, crear una seccion especifica en el indice.
