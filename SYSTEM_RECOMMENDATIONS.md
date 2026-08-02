# Recomendaciones para la evolución de DaImperium

## 1. Situación actual

DaImperium ya contiene una base funcional importante:

- Proyectos y dashboards generales y específicos.
- Datos económicos, presupuesto, ejecución y valores reales.
- Órdenes asociadas con proyectos.
- Tareas.
- Planificación y milestones.
- Empresas, áreas, países y ciudades.
- Usuarios, roles, permisos y acceso por empresa.
- Exportaciones a Excel.
- Catálogos administrables desde Filament.
- Preferencias de usuario.

Las siguientes recomendaciones buscan convertir el sistema en una herramienta de
control, seguimiento y toma de decisiones, no solamente en un repositorio de
información.

---

## 2. Prioridad alta

### 2.1. Auditoría de cambios

Es la mejora más recomendable para el estado actual del sistema.

Debe permitir responder:

- ¿Quién creó, modificó o eliminó un registro?
- ¿Cuándo ocurrió?
- ¿Qué campos cambiaron?
- ¿Cuál era el valor anterior y cuál es el nuevo?
- ¿Desde qué dirección IP o dispositivo se realizó?
- ¿A qué empresa y proyecto pertenecía el registro?

#### Tablas sugeridas

`audit_logs`

| Campo | Propósito |
|---|---|
| `id` | Identificador |
| `user_id` | Usuario que realizó la acción |
| `event` | `created`, `updated`, `deleted`, `restored`, `exported` |
| `auditable_type` | Tipo de modelo modificado |
| `auditable_id` | Registro modificado |
| `company_id` | Empresa relacionada |
| `project_id` | Proyecto relacionado, cuando aplique |
| `old_values` | Valores anteriores en JSON |
| `new_values` | Valores nuevos en JSON |
| `ip_address` | Dirección IP |
| `user_agent` | Navegador o cliente |
| `created_at` | Fecha y hora |

#### Alcance inicial recomendado

Auditar primero:

1. Proyectos.
2. Datos financieros.
3. Órdenes.
4. Milestones de proyectos.
5. Usuarios, roles y permisos.
6. Empresas y catálogos.
7. Exportaciones importantes.

No conviene guardar contraseñas, tokens, archivos binarios ni otros datos
sensibles dentro del historial.

#### Interfaz recomendada

- Recurso `Audit Logs` de solo lectura en Filament.
- Filtros por usuario, empresa, proyecto, módulo, evento y rango de fechas.
- Vista comparativa `Antes / Después`.
- Acceso restringido a administradores o a un permiso específico.
- Historial dentro de cada proyecto con sus cambios relacionados.

---

### 2.2. Configuración general del sistema

Crear un módulo `Settings` en Filament para evitar valores fijos dentro del
código.

#### Configuraciones recomendadas

- Nombre, logotipo y favicon del sistema.
- Moneda base.
- Monedas permitidas.
- Tipo de cambio predeterminado.
- Zona horaria.
- Formatos de fecha y números.
- Idioma.
- Cantidad predeterminada de elementos por página.
- Tamaño máximo de archivos.
- Extensiones permitidas.
- Correos para notificaciones.
- Días de anticipación para alertas.
- Tolerancia de desviación presupuestaria.
- Tolerancia de retraso de proyectos.
- Año fiscal.

#### Organización sugerida

`settings`

| Campo | Propósito |
|---|---|
| `group` | Grupo: general, finance, projects, notifications |
| `key` | Clave única |
| `value` | Valor |
| `type` | string, integer, decimal, boolean, JSON |
| `company_id` | Configuración global o específica por empresa |
| `is_public` | Indica si puede exponerse en la interfaz |

Se recomienda permitir valores globales y sobrescrituras por empresa.

---

### 2.3. Responsables y equipo del proyecto

Actualmente los permisos determinan qué puede hacer un usuario, pero también es
necesario saber quién es responsable de cada proyecto.

#### Funcionalidades

- Project Manager.
- Sponsor.
- Controller financiero.
- Comprador.
- Integrantes del equipo.
- Responsables de tareas y milestones.
- Fecha de asignación y retiro.

#### Tabla sugerida

`project_members`

- `project_id`
- `user_id`
- `role`
- `assigned_at`
- `removed_at`
- `is_primary`

Esto permitirá dashboards personales como **My Projects**, alertas dirigidas y
responsabilidades claras.

---

### 2.4. Centro de notificaciones y alertas

El sistema debería avisar automáticamente cuando:

- Un milestone está próximo a vencer.
- Un proyecto está retrasado.
- La fecha forecast cambió.
- El presupuesto supera un porcentaje definido.
- Una orden no tiene progreso durante varios días.
- Una tarea fue asignada o completada.
- Un proyecto cambia de estado.
- Se carga o reemplaza un documento importante.

#### Canales

- Notificaciones dentro de DaImperium.
- Correo electrónico.
- Futuramente Microsoft Teams.

Cada usuario debería poder configurar qué notificaciones desea recibir.

---

### 2.5. Historial financiero periódico

Los valores financieros actuales pueden cambiar y perderse las cifras
anteriores. Conviene guardar snapshots periódicos.

#### Tabla sugerida

`project_financial_snapshots`

- `project_id`
- `snapshot_date`
- `budgeted`
- `booked`
- `executed`
- `real_value`
- `remaining`
- `currency`
- `exchange_rate`

Esto permitirá:

- Curvas reales de evolución.
- Comparaciones mes a mes.
- Tendencias.
- Forecast financiero.
- Identificación del momento exacto de una desviación.

---

## 3. Prioridad media

### 3.1. Gestión documental

Agregar documentos relacionados con proyectos y órdenes:

- Cotizaciones.
- Purchase Orders.
- Facturas.
- Actas.
- Reportes.
- Evidencias.
- Contratos.
- Archivos técnicos.

#### Datos recomendados

- Categoría.
- Versión.
- Fecha del documento.
- Usuario que lo cargó.
- Estado: borrador, aprobado, rechazado, obsoleto.
- Comentario.
- Hash para verificar integridad.

No se debería reemplazar silenciosamente un documento: cada reemplazo debe crear
una nueva versión.

---

### 3.2. Comentarios y actividad del proyecto

Agregar una línea de tiempo por proyecto con:

- Comentarios.
- Cambios de estado.
- Milestones creados o completados.
- Documentos cargados.
- Alertas.
- Cambios financieros relevantes.

Esto ofrece una vista rápida de todo lo sucedido sin revisar varias tablas.

---

### 3.3. Workflow de aprobaciones

Para cambios sensibles se puede incluir aprobación:

- Creación o cambio de presupuesto.
- Cambio de fechas forecast.
- Cierre de proyecto.
- Eliminación de registros financieros.
- Cambios superiores a un monto definido.

#### Tablas sugeridas

- `approval_requests`
- `approval_steps`
- `approval_decisions`

El flujo puede depender de empresa, monto, tipo de proyecto o inversión.

---

### 3.4. Riesgos e incidencias

Crear un registro de riesgos por proyecto:

- Descripción.
- Probabilidad.
- Impacto.
- Nivel calculado.
- Responsable.
- Plan de mitigación.
- Fecha objetivo.
- Estado.

También conviene separar las incidencias que ya ocurrieron de los riesgos que
todavía son potenciales.

---

### 3.5. Dependencias entre milestones

Además del orden mensual, permitir relaciones:

- Un milestone no puede comenzar hasta completar otro.
- Dependencia obligatoria u opcional.
- Retraso propagado a hitos posteriores.
- Camino crítico básico.

Esto haría más útil la planificación de hasta dos años.

---

### 3.6. Indicadores de salud del proyecto

Crear un indicador visual:

- **Green:** dentro de fechas y presupuesto.
- **Amber:** existe una desviación moderada.
- **Red:** retraso o desviación crítica.

El resultado debería calcularse mediante reglas configurables, no elegirse
manualmente.

Ejemplos:

- Desviación de presupuesto.
- Días de retraso.
- Milestones vencidos.
- Órdenes pendientes.
- Falta de actualización reciente.

---

### 3.7. Cierre formal del proyecto

Agregar un proceso de cierre:

- Confirmación de milestones completados.
- Conciliación financiera.
- Órdenes cerradas.
- Documentos obligatorios.
- Lecciones aprendidas.
- Fecha y usuario que aprobó el cierre.

El estado `Closed` debería depender de estas validaciones.

---

## 4. Configuraciones y catálogos recomendados

Además de países, ciudades, empresas, áreas y milestones:

- Tipos de proyecto.
- Clasificaciones de inversión.
- Justificaciones.
- Estados del proyecto.
- Monedas.
- Tipos de cambio.
- Categorías de documento.
- Tipos de orden.
- Estados de orden.
- Prioridades.
- Tipos de riesgo.
- Motivos de cambio de fecha.
- Motivos de cambio presupuestario.
- Roles dentro del proyecto.
- Reglas de alertas.

### Recomendación sobre enums y tablas

Usar **enum** cuando:

- El catálogo es pequeño.
- Cambia muy rara vez.
- El valor afecta lógica técnica del sistema.

Usar **tabla administrable** cuando:

- El usuario necesita crear o editar valores desde Filament.
- El valor necesita color, orden, estado activo o traducción.
- Puede variar por empresa.

Para catálogos de negocio, una tabla suele ser más flexible que un enum.

---

## 5. Reportes y dashboards recomendados

### Dashboard ejecutivo

- Proyectos por estado.
- Presupuesto, booked, executed y real.
- Desviación financiera.
- Proyectos retrasados.
- Milestones vencidos.
- Top proyectos por presupuesto.
- Distribución por empresa, área e inversión.

### Dashboard de planificación

- Milestones por mes.
- Proyectos sin planificación.
- Cumplimiento de hitos.
- Próximos vencimientos.
- Retrasos acumulados.

### Dashboard financiero

- Evolución mensual.
- Forecast frente a real.
- Variación por proyecto.
- Presupuesto restante.
- Concentración por proveedor.
- Conversión por moneda y tipo de cambio utilizado.

### Dashboard personal

- Mis proyectos.
- Mis tareas.
- Mis milestones.
- Aprobaciones pendientes.
- Alertas sin leer.

---

## 6. Calidad y gobierno de datos

### Validaciones recomendadas

- Códigos únicos.
- Fechas coherentes.
- Forecast start menor que forecast end.
- Close date posterior al inicio.
- Montos no negativos.
- Monedas obligatorias.
- Proyecto y orden pertenecientes a la misma empresa.
- Milestones dentro de los dos años permitidos.
- Prohibir eliminación cuando existan relaciones críticas.

### Importaciones

- Vista previa antes de importar.
- Reporte de errores por fila.
- Modo simulación sin guardar.
- Detección de duplicados.
- Registro de quién importó el archivo.
- Posibilidad de revertir una importación completa.

---

## 7. Seguridad y operación

### Recomendaciones

- Autenticación de dos factores para administradores.
- Sesiones con expiración configurable.
- Registro de accesos y fallos de autenticación.
- Políticas por empresa en todos los módulos.
- Permisos separados para ver, crear, editar, eliminar y exportar.
- Eliminación lógica para datos importantes.
- Copias de seguridad automáticas.
- Prueba periódica de restauración.
- Monitoreo de jobs fallidos.
- Política de retención de auditoría y documentos.

---

## 8. Integraciones futuras

- ERP o SAP para órdenes y valores reales.
- Microsoft Teams para alertas.
- Outlook para recordatorios.
- SharePoint para documentos.
- Power BI para análisis corporativo.
- API para importar y consultar proyectos.

Antes de integrar conviene definir identificadores externos estables, fecha de
última sincronización y registro de errores.

---

## 9. Hoja de ruta recomendada

### Fase 1 — Control y trazabilidad

1. Auditoría.
2. Configuración general.
3. Responsables del proyecto.
4. Notificaciones internas.
5. Validaciones de integridad.

### Fase 2 — Seguimiento

1. Historial financiero mensual.
2. Riesgos e incidencias.
3. Actividad y comentarios.
4. Gestión documental con versiones.
5. Indicador de salud del proyecto.

### Fase 3 — Gobierno

1. Aprobaciones.
2. Cierre formal.
3. Reglas configurables por empresa.
4. Dashboards ejecutivos y personales.

### Fase 4 — Integraciones

1. SAP o ERP.
2. Teams y Outlook.
3. SharePoint.
4. API.
5. Power BI.

---

## 10. Recomendación inmediata

El siguiente módulo debería ser **Auditoría**.

La aplicación ya permite modificar proyectos, finanzas, órdenes, milestones,
usuarios y permisos. A medida que más usuarios utilicen el sistema será
indispensable conocer el origen de cada cambio.

Una primera versión puede enfocarse en:

1. Proyectos.
2. Datos financieros.
3. Milestones.
4. Usuarios, roles y permisos.

Después se puede ampliar a órdenes, catálogos, exportaciones, accesos e
importaciones.

La segunda mejora recomendada es **Settings**, porque permitirá que las próximas
funcionalidades usen reglas configurables en lugar de valores escritos
directamente en el código.
