# Admin Filament

El proyecto usa Filament para administrar tablas maestras desde un panel interno.

## Acceso
- Requiere usuarios activos con `is_staff=true`.
- Validacion: `User::canAccessPanel`.

Referencias:
- [../app/Models/User.php](../app/Models/User.php)

## Recursos disponibles
### Umbrales (aforos, roeas, marcos)
- Resource: [../app/Filament/Resources/UmbralesRans/UmbralesRanResource.php](../app/Filament/Resources/UmbralesRans/UmbralesRanResource.php)
- Form: [../app/Filament/Resources/UmbralesRans/Schemas/UmbralesRanForm.php](../app/Filament/Resources/UmbralesRans/Schemas/UmbralesRanForm.php)
- Table: [../app/Filament/Resources/UmbralesRans/Tables/UmbralesRansTable.php](../app/Filament/Resources/UmbralesRans/Tables/UmbralesRansTable.php)

Campos clave:
- `ur_codigo`: codigo de estacion.
- `ur_nombre`, `ur_rio`, `ur_provincia`, `ur_municipio`.
- `ur_umbral1`, `ur_umbral2`, `ur_umbral3`.
- `ur_tag_ip21`, `ur_tag_ip21_caudal`, `ur_tag_digital_ip21`.
- `ur_activo` y `ur_ultimo_nivel_alerta`.
- `ur_comunidad_autonoma_id`, `ur_zona_explotacion`, `ur_ccaa_influencia`.

### Embalses
- Resource: [../app/Filament/Resources/EmbalsesRans/EmbalsesRanResource.php](../app/Filament/Resources/EmbalsesRans/EmbalsesRanResource.php)
- Form: [../app/Filament/Resources/EmbalsesRans/Schemas/EmbalsesRanForm.php](../app/Filament/Resources/EmbalsesRans/Schemas/EmbalsesRanForm.php)
- Table: [../app/Filament/Resources/EmbalsesRans/Tables/EmbalsesRansTable.php](../app/Filament/Resources/EmbalsesRans/Tables/EmbalsesRansTable.php)

Campos clave:
- `er_codigo`, `er_nombre`, `er_rio`, `er_provincia`, `er_municipio`.
- `er_umbral1`, `er_umbral2`, `er_umbral3`.
- `er_tag_ip21`, `er_tag_volumen`, `er_tag_digital_ip21`.
- `er_activo`, `er_ultimo_nivel_alerta`.
- `er_comunidad_autonoma_id`, `er_ccaa_influencia`, `er_titularidad`.

### Usuarios
- Resource: [../app/Filament/Resources/Users/UserResource.php](../app/Filament/Resources/Users/UserResource.php)
- Form: [../app/Filament/Resources/Users/Schemas/UserForm.php](../app/Filament/Resources/Users/Schemas/UserForm.php)
- Table: [../app/Filament/Resources/Users/Tables/UsersTable.php](../app/Filament/Resources/Users/Tables/UsersTable.php)

### Grupos
- Resource: [../app/Filament/Resources/Groups/GroupResource.php](../app/Filament/Resources/Groups/GroupResource.php)
- Form: [../app/Filament/Resources/Groups/Schemas/GroupForm.php](../app/Filament/Resources/Groups/Schemas/GroupForm.php)
- Table: [../app/Filament/Resources/Groups/Tables/GroupsTable.php](../app/Filament/Resources/Groups/Tables/GroupsTable.php)

## Permisos estilo Django
- El modelo User implementa permisos directos y por grupo.
- Trait disponible para mapear acciones a `view/add/change/delete`.

Referencia:
- [../app/Filament/Traits/HasDjangoPermissions.php](../app/Filament/Traits/HasDjangoPermissions.php)

## Impacto en la operacion
- Cambios de umbrales alteran niveles de alerta.
- Cambios de tags afectan la lectura desde la API SAIH.
- Activar o desactivar estaciones impacta el panel y el mapa.

## Buenas practicas
- Validar tags antes de activar estaciones.
- Mantener umbrales consistentes (no dejar los tres a cero salvo casos especiales).
- Evitar cambios masivos en horas de alta demanda.

## Ver tambien
- [Indice](README.md)
- [Arquitectura y datos](03-arquitectura-datos.md)
