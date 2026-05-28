# Frontend y tiempo real

## Assets y build
- Vite y Tailwind gestionan assets.
- Entradas principales:
  - [../resources/css/app.css](../resources/css/app.css)
  - [../resources/js/app.js](../resources/js/app.js)
- Build: `npm run build` (salida en la carpeta public, subcarpeta build).

Referencias:
- [../vite.config.js](../vite.config.js)
- [../resources/js/bootstrap.js](../resources/js/bootstrap.js)
- [../resources/js/echo.js](../resources/js/echo.js)

## Layout y navegacion
- Plantilla base con cabecera, menu lateral y buscador global.
- Incluye gestion de navegacion interna y boton volver.

Referencia:
- [../resources/views/auth/plantilla.blade.php](../resources/views/auth/plantilla.blade.php)

## Panel principal
- Resumen de alertas por nivel.
- Pestanas para aforos y embalses.
- Filtros por CCAA y por nivel.

Referencias:
- [../resources/views/auth/inicio_umbrales.blade.php](../resources/views/auth/inicio_umbrales.blade.php)
- [../resources/views/auth/tabla_inicio.blade.php](../resources/views/auth/tabla_inicio.blade.php)

## Estado actual por CCAA
- Vista agrupada por comunidad.
- Auto refresco cada 5 minutos.

Referencia:
- [../resources/views/auth/vista_estadoActual.blade.php](../resources/views/auth/vista_estadoActual.blade.php)

## Episodios y mini graficos
- En el detalle se generan mini graficos por estacion.
- Modal con grafico ampliado y rangos configurables.

Referencia:
- [../resources/views/auth/episodios_detalle.blade.php](../resources/views/auth/episodios_detalle.blade.php)

## Grafico dedicado
- Vista independiente con ApexCharts y selector de rango.

Referencia:
- [../resources/views/auth/grafico.blade.php](../resources/views/auth/grafico.blade.php)

## Mapa global
- Leaflet con clustering y filtros en cliente.
- GeoJSON para delinear la cuenca.

Referencias:
- [../resources/views/auth/mapa_global.blade.php](../resources/views/auth/mapa_global.blade.php)
- [../public/geojson/CuencaTajoElegido.geojson](../public/geojson/CuencaTajoElegido.geojson)

## Tiempo real (Reverb + Echo)
- Echo se configura en [../resources/js/echo.js](../resources/js/echo.js).
- Evento de backend: [../app/Events/NuevoCambioRecibido.php](../app/Events/NuevoCambioRecibido.php).
- El layout escucha el canal `panel-alertas` y recarga la vista.

Referencias:
- [../resources/views/auth/plantilla.blade.php](../resources/views/auth/plantilla.blade.php)
- [../config/broadcasting.php](../config/broadcasting.php)
- [../config/reverb.php](../config/reverb.php)

## Recursos estaticos
- Logo institucional usado en la cabecera.

Referencia:
- [../public/images/logo-web-CHT-centenario.png](../public/images/logo-web-CHT-centenario.png)

## Ver tambien
- [Indice](README.md)
- [Arquitectura y datos](03-arquitectura-datos.md)
