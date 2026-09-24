# Revisión de temas y uso móvil

## Implementación

- La apariencia se gestiona exclusivamente desde Profile y conserva la preferencia existente. En su ubicación anterior, la navegación ofrece Admin solo a usuarios activos autorizados mediante `canAccessPanel`; administración ofrece Sistema. Ambos accesos son compactos en móvil.
- El selector compartido de registros usa borde simple, superficie neutra y valor legible, con espacio reservado para la flecha. Planning corrige el fondo oscuro de las celdas de nombre, que antes quedaba sobrescrito por una variable en línea.
- Projects muestra iconos accesibles en móvil para exportar, crear y gestionar vistas; conserva etiquetas en tablet/escritorio.

- El panel normal, acceso y bienvenida reutilizan la clave `theme`, los valores `light` / `dark` / `system`, el evento `theme-changed` y la clase `dark` de Filament. La selección se conserva entre ambos paneles y sigue el sistema cuando corresponde.
- Los colores compartidos de Tailwind reciben valores oscuros consistentes. Las variantes `dark:` explícitas conservan prioridad. Las tablas reutilizan sus variables existentes; gráficos, tooltips, paginadores, formularios y pantallas de carga tienen tratamiento de tema.
- Los botones compartidos con fondos claros ajustan el color del texto para conservar contraste. El QR de autenticación mantiene un fondo blanco para permitir su lectura.
- Los bloques grandes de filtros empiezan contraídos en teléfonos y se amplían en escritorio. Se agregaron chips removibles y limpieza mediante las propiedades y acciones existentes.
- KPIs en dos columnas en móvil, separación reducida, acciones conocidas compactas con nombre accesible, pestañas de formulario en una fila y pies de modal que permiten varias acciones sin ocupar filas enteras innecesariamente.
- Projects, Data, Orders, Planning, el resumen de Activities y las tablas administrativas ofrecen presentación compacta y un control **Todas las columnas**. Se conserva el acceso a la información secundaria y no se sobrescriben las preferencias de columnas guardadas.
- Las tablas administrativas usan los atributos y acciones responsive de Filament; no se sustituyeron sus componentes ni sus CRUDs.
- Los gráficos de proyecto sin series muestran un estado vacío, evitando una excepción de la librería. Los eventos vacíos `wire:click.stop` de los modales de planificación se sustituyeron por eventos de Alpine para detener la propagación sin invocar un método inexistente.

No se modificaron importes, cálculos, consultas de negocio ni permisos. La limpieza del dashboard de proyecto también devuelve la moneda a su valor inicial. En Projects y Planning, limpiar filtros incluye la búsqueda; Projects sincroniza el campo de búsqueda mediante sus eventos Livewire existentes.

## Cobertura

Se revisaron rutas, layouts, vistas compartidas, recursos y esquemas de Filament, incluyendo pantallas secundarias y modales.

| Área | Pantallas y estados |
| --- | --- |
| Panel normal | Dashboard, Review, Summary, Planning (alias Planification), Activities, Projects, Orders, Tools, Templates y Profile. |
| Proyecto | Dashboard individual, datos y órdenes; columnas compactas/completas; formulario con Details, Dates y Documents. |
| Administración | Inicio, Users, Projects, Companies, Roles, Permissions, Milestones, Excel Templates, Areas, Countries, Cities, Brand Settings, Project Rate Settings y Audit Logs. Listados y formularios de creación/edición disponibles en los datos de prueba. |
| Modales | Crear proyecto, propietario, fechas/documentos, PDA, ideas, certificado de entrega, importación, hitos, actividad semanal, edición y confirmación de eliminación, datos y proveedor. |
| Acceso | Bienvenida, login normal/administrativo, registro, recuperación/restablecimiento, confirmación de contraseña, doble factor y error 404. |
| Compartidos | Menú, sidebar, acciones, filtros desplegables, chips, paginación, cantidad por página, tarjetas, tablas, gráficos y sus tooltips. |

## Método de comprobación

Chrome headless con perfil de navegador y base SQLite de prueba aislados, sin modificar datos reales. Se alternan **Light / Dark** en **320, 375, 430, 768, 1024 y 1440 px**. Se usan nombres y descripciones largos, estados vacíos y valores financieros de prueba. Las capturas y mediciones verifican anchura de página, elementos fuera del viewport y excepciones JavaScript.

Pruebas de interacción adicionales:

- Verificación final de Profile, Planning, Projects y administración en ambos temas y seis anchos: sin desbordamiento de página ni excepciones JavaScript. Selección real de Dark desde Profile y persistencia al navegar entre paneles. Render del acceso Admin comprobado con usuario autorizado, sin permiso e inactivo.

- Filtros contraídos al entrar desde móvil; expansión, aparición del chip de búsqueda y eliminación del filtro.
- Alternancia compacta/completa de tablas de Projects y administración, sin modificar preferencias guardadas.
- Persistencia del tema al navegar desde administración al panel normal y en sentido contrario.
- Apertura de modales y submodales en ambos temas; formularios y confirmaciones de planificación.

Los artefactos locales están en `storage/app/mobile-audit/`: `themes-*.log`, `theme-results-*.json`, capturas `theme-*` / `check-*`, `theme-controls.log`, `planning-actions-verified.log` y reportes PHPUnit. Esta carpeta está excluida de Git.

## Límites

- Build de producción: correcto.
- Suite focalizada final: **43 tests y 543 aserciones, sin fallos**, incluyendo dos pruebas de sincronización entre los filtros y el buscador de Projects.
- Suite general ejecutada: **140 tests, 1443 aserciones, 11 fallos**. Se mantienen los mismos fallos registrados en la revisión anterior: autenticación/redirecciones (7), expectativas de Planning (2), formato financiero de Projects (1) y columnas de Data (1). No se declara aprobada la suite general.

La emulación no sustituye las pruebas en dispositivos físicos iOS/Android. La cobertura visual no certifica todas las combinaciones posibles de permisos, datos, validaciones ni integraciones externas. Las tablas completas y las líneas temporales conservan desplazamiento horizontal local cuando el contenido lo requiere.
