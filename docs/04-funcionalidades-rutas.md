# Funcionalidades y rutas

Nota: las rutas no usan middleware explicito. El acceso se controla por sesion y por validaciones internas de cada controlador.

## Autenticacion y sesion
Rutas principales:
- `/login` GET: formulario y regeneracion de CSRF.
- `/login` POST: valida `username` y `password`.
- `/cerrarSesion` GET: invalida sesion.

Referencias:
- [../routes/web.php](../routes/web.php)
- [../app/Http/Controllers/AuthController.php](../app/Http/Controllers/AuthController.php)
- [../resources/views/auth/login_umbrales.blade.php](../resources/views/auth/login_umbrales.blade.php)

## Layout y navegacion
- La plantilla base define cabecera, menu lateral y buscador global.
- Incluye recarga automatica cuando llega el evento `NuevoCambioRecibido`.

Referencia:
- [../resources/views/auth/plantilla.blade.php](../resources/views/auth/plantilla.blade.php)

## Panel principal (estado actual)
- Dos pestanas: Aforos en rio y Embalses.
- Filtros por CCAA y por nivel de alerta.
- Resumen superior con totales por nivel.

Referencias:
- [../app/Http/Controllers/EpisodioController.php](../app/Http/Controllers/EpisodioController.php)
- [../resources/views/auth/inicio_umbrales.blade.php](../resources/views/auth/inicio_umbrales.blade.php)
- [../resources/views/auth/tabla_inicio.blade.php](../resources/views/auth/tabla_inicio.blade.php)

## Estado actual por CCAA
- Vista agrupada por comunidad autonoma.
- Auto refresco cada 5 minutos.

Referencia:
- [../resources/views/auth/vista_estadoActual.blade.php](../resources/views/auth/vista_estadoActual.blade.php)

## Episodios (listados)
- Activos y historicos por cuenca o por CCAA.
- En historicos, filtro por nombre.
- Muestra estaciones vinculadas y alarmadas, con panel de detalle.

Referencias:
- [../app/Http/Controllers/EpisodioController.php](../app/Http/Controllers/EpisodioController.php)
- [../resources/views/auth/episodios_lista.blade.php](../resources/views/auth/episodios_lista.blade.php)

## Episodios (detalle)
- Lista de estaciones con valor actual y umbrales.
- Mini graficos por estacion y grafico ampliado en modal.
- Acciones de staff: renombrar y cerrar episodio.

Referencias:
- [../app/Http/Controllers/EpisodioController.php](../app/Http/Controllers/EpisodioController.php)
- [../resources/views/auth/episodios_detalle.blade.php](../resources/views/auth/episodios_detalle.blade.php)

## Graficos
- Vista dedicada por estacion con selector de rango.
- Endpoint JSON: `/api/historial/{codigo}`.
- Parametros: `dias` (1, 7, 10, 30, 365, all) y `episodio_id`.
- Para dias 30 o all se agregan medias horarias.
- Para aforos se devuelve `maximo_historico`.

Referencias:
- [../app/Http/Controllers/GraficoController.php](../app/Http/Controllers/GraficoController.php)
- [../resources/views/auth/grafico.blade.php](../resources/views/auth/grafico.blade.php)

## Busqueda global
- Tokens por nombre, codigo, rio o provincia.
- Filtros: tipo, alerta, activo/inactivo, episodios activos o historicos.
- Ordena resultados por puntuacion y nivel de alerta.

Referencia:
- [../app/Services/BuscadorService.php](../app/Services/BuscadorService.php)

## Mapa global
- Leaflet con clustering.
- Filtros por texto, tipo, CCAA y nivel de alerta.
- GeoJSON de la cuenca del Tajo.

Referencias:
- [../resources/views/auth/mapa_global.blade.php](../resources/views/auth/mapa_global.blade.php)
- [../public/geojson/CuencaTajoElegido.geojson](../public/geojson/CuencaTajoElegido.geojson)

## Emergencias
- Formulario con CCAA, provincias, nivel, fecha y hora.
- Dos modos de documentacion: PDF adjunto o PDF generado desde texto.
- Validaciones clave:
  - Fecha no futura.
  - Hora no futura si la fecha es hoy.
  - PDF con maximo 1.9 MB.
- En modo plan, la documentacion puede ser opcional.

Referencias:
- [../app/Http/Controllers/EmergenciaController.php](../app/Http/Controllers/EmergenciaController.php)
- [../resources/views/auth/situacion_formulario.blade.php](../resources/views/auth/situacion_formulario.blade.php)
- [../resources/views/auth/plantilla_pdf.blade.php](../resources/views/auth/plantilla_pdf.blade.php)

## Plan de emergencia
- Vista matricial por CCAA y provincias.
- Colores por nivel y historial por dia.
- Celdas con cambios destacados.

Referencia:
- [../resources/views/auth/vista_PlanEmergencia.blade.php](../resources/views/auth/vista_PlanEmergencia.blade.php)

## Usuarios
- Solo superusuario puede crear, editar o eliminar.
- No se permite borrar el usuario de la sesion actual.

Referencias:
- [../app/Http/Controllers/UserController.php](../app/Http/Controllers/UserController.php)
- [../resources/views/auth/confUsuarios.blade.php](../resources/views/auth/confUsuarios.blade.php)
- [../resources/views/auth/editarUsuario.blade.php](../resources/views/auth/editarUsuario.blade.php)

## Codigo legacy
Controladores con logica comentada en rutas:
- [../app/Http/Controllers/AforoRiosController.php](../app/Http/Controllers/AforoRiosController.php)
- [../app/Http/Controllers/EmbalsesController.php](../app/Http/Controllers/EmbalsesController.php)
- [../app/Http/Controllers/MarcoControlController.php](../app/Http/Controllers/MarcoControlController.php)
- [../app/Http/Controllers/RoeasController.php](../app/Http/Controllers/RoeasController.php)

## Ver tambien
- [Indice](README.md)
- [Arquitectura y datos](03-arquitectura-datos.md)
