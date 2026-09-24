# Revisión de internacionalización

Fecha: 24 de septiembre de 2026.

## Implementación

- Se conserva la preferencia `locale` de `UserPreference` y los idiomas existentes: inglés, español e italiano.
- La interfaz utiliza los catálogos JSON existentes y los grupos de traducción de cada módulo. `ui.php` reúne plurales y controles de gráficos compartidos.
- El middleware aplica el idioma a Laravel, Carbon y al formateador `Number`. Las notificaciones utilizan la preferencia del destinatario mediante `HasLocalePreference`.
- La sesión conserva el idioma para las pantallas públicas de autenticación. Un usuario diferente recibe su propia preferencia, sin heredar la del usuario anterior.
- Los estados enumerados conservan sus valores originales; sus etiquetas se traducen al mostrarse. Los gráficos conservan las claves de colores, importes y relaciones entre series y ejes.
- Los filtros traducen etiquetas y opciones del sistema, conservando literalmente búsquedas, nombres de usuarios, plantas y demás datos guardados.
- Los informes Excel traducen encabezados, hojas, instrucciones y etiquetas. Las fórmulas referencian los nombres traducidos de sus hojas y estados. Los encabezados técnicos y la hoja `Project Data` de los archivos de reimportación mantienen su contrato original.

## Ámbito revisado

Navegación, bienvenida, autenticación, perfil, Dashboard, Review, Summary, Planning, Activities, Projects y sus detalles, Data, Orders, Tools, Templates, componentes compartidos, filtros, paginación, validaciones, alertas, modales y exportaciones.

Panel administrativo: inicio, usuarios, proyectos, empresas, roles, permisos, hitos, plantillas Excel, áreas, países, ciudades, límites de tasas, marca y auditoría; formularios y componentes compartidos de Filament.

No se traducen marcas, nombres propios, códigos, textos de actividades, nombres de archivos ni contenido proporcionado por usuarios. Los controles nativos de fecha/semana también dependen del idioma del navegador y del sistema operativo.

## Verificación

- Recorrido de 25 rutas en inglés, español e italiano: sin errores de JavaScript registrados.
- Formularios de perfil y administración comprobados adicionalmente en español e italiano a 375 px.
- Ocho vistas de modales revisadas a 320, 375, 430, 768 y 1024 px, en claro y oscuro, sin errores JavaScript ni desbordamiento de la página. La medición detecta también elementos del fondo y textos ocultos detrás de los modales.
- Detalle de proyecto y sus cinco gráficos comprobados en italiano entre 320 y 1440 px después de corregir las claves de colores.
- Compilación Vite y revisión de sintaxis PHP correctas.
- Diez pruebas específicas de localización y compatibilidad de reimportación: 3763 aserciones correctas. Cubren claves y parámetros de los catálogos, referencias de traducción en el código, preferencias, fechas, validación, datos literales, gráficos y fórmulas Excel.
- La suite completa ejecutada durante la revisión mantuvo los 11 fallos preexistentes: redirecciones de cuentas inactivas en pruebas de autenticación/perfil, dos expectativas visuales de Planning, formato de KPI y orden de columnas de Data. No se modificaron esas funcionalidades para resolverlos.
- Se repitieron las búsquedas de texto visible en Blade, PHP y JavaScript. Los hallazgos restantes revisados corresponden a código, comentarios, símbolos, identificadores técnicos y contenido literal que debe conservarse.

## Mantenimiento

Traducir al presentar los datos, sin utilizar etiquetas traducidas como claves de negocio. Reutilizar las claves de los módulos existentes y completar los tres idiomas. `tests/Feature/LocalizationTest.php` comprueba las referencias literales y la coherencia de los catálogos.
