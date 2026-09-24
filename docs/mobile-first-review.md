# Revisión Mobile First

## Alcance y cambios

Se conservaron las consultas, permisos, cálculos y componentes existentes. Los cambios de esta revisión afectan a la presentación y a las interacciones de la interfaz.

| Área | Ajustes |
| --- | --- |
| Componentes compartidos | Tipografía móvil legible, controles táctiles, botones con texto multilínea, grids flexibles y eliminación de restricciones de ancho que desbordaban. |
| Navegación | Menú móvil con desplazamiento interno, cierre con Escape y textos largos adaptables. |
| Dashboard, Review y Summary | KPIs reorganizables, filtros compactos que se apilan, tarjetas y gráficos adaptables. Se conservan los formatos monetarios del gráfico al cambiar de breakpoint. |
| Activities | Filtros adaptables, primera columna del resumen de usuarios fija dentro de su tabla, nombres completos y paginaciones sin salto de desplazamiento. |
| Planning | Filtros sin desbordamiento, columnas fijas reservadas para pantallas amplias, actividades con acciones adaptables e información accesible mediante toque. |
| Projects y sus páginas de dashboard, datos y órdenes | Tablas con desplazamiento horizontal local, nombres largos legibles, acciones y KPIs adaptables, pestañas del formulario y avisos ajustados. |
| Tools y Templates | Pasos apilables, previsualización de Excel con desplazamiento local, controles de paginación adaptables y nombres/descripciones completos. |
| Modales | Límites basados en la altura visible, desplazamiento interno, bloqueo del fondo, acciones que se reorganizan y tratamiento para pantallas bajas/teclado. Aplicado también a los modales manuales de documentos, importación, ideas, entrega, propietarios y proveedores. |
| Perfil y acceso | Textos largos ajustables y controles táctiles mediante el layout compartido. |
| Administración Filament | Se reutiliza su distribución responsive; se amplían controles táctiles y se adapta el detalle de auditoría. |

Los filtros desplegables calculan el espacio disponible arriba/abajo y reaccionan al viewport visual. Se corrigió además una referencia a `$cleanup` que generaba errores de JavaScript al inicializarlos.

## Verificación

Pruebas en Chrome headless con una base SQLite y un perfil de navegador aislados, sin modificar datos reales. Anchos: **320, 375, 430, 768, 1024 y 1440 px**. Emulación táctil por debajo de 1024 px.

- Recorrido de Dashboard, Review, Summary, Planning, Activities, Projects, Orders, Tools, Templates, Profile y confirmación de contraseña.
- Recorrido de bienvenida, inicio de sesión, registro, recuperación/restablecimiento de contraseña y pantalla de doble factor en los seis anchos, sin desbordamientos ni errores JavaScript.
- Recorrido de dashboard/datos/órdenes de un proyecto con nombres largos y datos financieros de prueba.
- Recorrido administrativo: inicio, usuarios, proyectos, empresas, roles, permisos, hitos, plantillas Excel, áreas, países, ciudades, marca, tarifas y auditoría; formularios de creación correspondientes.
- Comprobación de dimensiones y capturas de los modales principales de proyectos y planificación, y del formulario de datos.
- Carga real de un Excel de prueba, análisis y previsualización; expansión de archivos y descripciones.
- Verificación de las dos paginaciones de Activities: la posición vertical antes/después permanece idéntica.
- Inspección visual de gráficos, leyendas y formatos monetarios en móvil, tablet y escritorio.
- Prueba adicional del formulario de proyecto a **375 × 500 px**, simulando menor altura disponible.

No se detectaron desbordamientos horizontales de página ni excepciones JavaScript en los recorridos completados. Las tablas anchas conservan desplazamiento horizontal dentro de su contenedor.

## Comprobaciones automatizadas y límites

- Build de producción: correcto.
- Suite focalizada: **41 tests, 533 aserciones, sin fallos** (Activities, Summary, columnas de Projects, propietario/orden SAP, proveedores, Tools, Templates y Review).
- Suite completa: **140 tests, 1443 aserciones, 11 fallos**. Persisten discrepancias de autenticación/redirecciones (7), expectativas de Planning (2), formato financiero de Projects (1) y columnas de datos (1). No se modificaron reglas de negocio para forzar estas expectativas; la suite completa no se declara aprobada.
- Evidencias locales: `storage/app/mobile-audit/` contiene capturas, resultados JSON, logs de navegador y reportes PHPUnit. Es una carpeta ignorada por Git.
- La emulación no sustituye una prueba en dispositivos físicos iOS/Android. No se verificaron exhaustivamente todas las combinaciones de permisos, validaciones y estados de cada modal, ni los servicios externos de autenticación/correo.
