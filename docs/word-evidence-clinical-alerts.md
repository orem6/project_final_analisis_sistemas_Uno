# Documento de Evidencia — Módulo Alertas Clínicas Básicas

> Copia este contenido a tu documento de Word y completa los datos de portada e índice.

---

## Portada

**Nombre del estudiante:** [Tu nombre]
**Módulo asignado:** Alertas Clínicas Básicas (Módulo #18)
**Curso:** Análisis de Sistemas I
**Fecha:** [Fecha de entrega]

---

## Índice

1. Enlace al repositorio fork
2. Descripción del módulo trabajado
3. Diagramas UML
   - Diagrama de Casos de Uso
   - Diagrama de Clases
   - Diagrama de Secuencia
4. Explicación de los cambios realizados
5. Commits principales que evidencian Sprint 1, 2 y 3

---

## 1. Enlace al repositorio fork

```
[URL del repositorio forkeado en GitHub]
```

---

## 2. Descripción del módulo trabajado

### Módulo: Alertas Clínicas Básicas

El módulo **Alertas Clínicas Básicas** permite a los profesionales de la salud (médicos, enfermeras) registrar valores clínicos de pacientes y recibir alertas visuales inmediatas cuando dichos valores se encuentran fuera de los rangos normales.

### Funcionalidades implementadas

1. **Gestión de pacientes**: Registro y listado de pacientes con datos básicos (nombre, documento, fecha de nacimiento, etc.)
2. **Registro de valores clínicos**: Formulario para ingresar mediciones de 7 parámetros clínicos con selección de tipo y validación automática
3. **Evaluación automática de alertas**: Al guardar un registro, el sistema compara el valor contra los rangos configurados y determina la severidad
4. **Alertas visuales**: Badges de colores (verde, amarillo, naranja, rojo) en la tabla de registros, con tooltips informativos
5. **Notificaciones toast**: Sistema de notificaciones animadas al crear registros críticos
6. **Dashboard de resumen**: Tarjetas con conteo de alertas activas por severidad
7. **Historial por paciente**: Timeline con todos los registros clínicos de un paciente y filtro por tipo
8. **Filtros avanzados**: Filtros por severidad, tipo de registro y rango de fechas

### Parámetros clínicos soportados

| Parámetro | Unidad | Rango normal | Alerta baja | Alerta alta | Crítico |
|-----------|--------|-------------|-------------|-------------|---------|
| Temperatura Corporal | °C | 36.1 – 37.2 | 35.0 – 36.0 | 37.3 – 38.5 | < 35.0 o > 38.5 |
| Presión Arterial Sistólica | mmHg | 90 – 120 | 80 – 89 | 121 – 140 | < 80 o > 140 |
| Presión Arterial Diastólica | mmHg | 60 – 80 | 50 – 59 | 81 – 90 | < 50 o > 90 |
| Frecuencia Cardíaca | lpm | 60 – 100 | 50 – 59 | 101 – 120 | < 50 o > 120 |
| Frecuencia Respiratoria | rpm | 12 – 20 | 10 – 11 | 21 – 25 | < 10 o > 25 |
| Saturación de Oxígeno | % | 95 – 100 | 90 – 94 | 85 – 89 | < 85 |
| Glucosa en Ayuno | mg/dL | 70 – 100 | 60 – 69 | 101 – 126 | < 60 o > 126 |

---

## 3. Diagramas UML

### 3.1 Diagrama de Casos de Uso

![Diagrama de Casos de Uso](uml-clinical-alerts.md#1-diagrama-de-casos-de-uso)

(Ver el archivo `docs/uml-clinical-alerts.md` del repositorio para el diagrama en Mermaid)

**Actores:**
- **Médico**: Puede registrar pacientes, valores clínicos, ver alertas, ver historial y resumen.
- **Enfermera**: Mismos permisos que Médico para registrar y consultar.
- **Admin**: Además de lo anterior, puede configurar rangos clínicos y gestionar usuarios.

**Casos de uso principales:**
- Registrar Paciente
- Registrar Valor Clínico (extiende a Ver Alertas Activas)
- Ver Alertas Activas
- Ver Historial del Paciente
- Ver Resumen de Alertas
- Configurar Rangos Clínicos (solo Admin)
- Gestionar Usuarios (solo Admin)

### 3.2 Diagrama de Clases

**Clases del módulo:**

| Clase | Descripción | Atributos clave |
|-------|-------------|-----------------|
| **Patient** | Representa un paciente del hospital | id, tenant_id, document_number, name, last_name, birth_date |
| **ClinicalRecord** | Registro de un valor clínico | id, tenant_id, patient_id, record_type, value, unit, severity |
| **ClinicalRange** | Define rangos normales y críticos | id, tenant_id, record_type, min_value_normal, max_value_normal, critical_low, critical_high |
| **ClinicalAlertService** | Servicio con lógica de evaluación | evaluate(), getAlertsSummary(), getActiveAlerts() |
| **ClinicalAlertController** | Controlador de la API REST | patientsIndex(), recordsStore(), alertsSummary(), etc. |

**Relaciones:**
- Tenant 1 → * Patient, ClinicalRecord, ClinicalRange
- Patient 1 → * ClinicalRecord
- User 1 → * ClinicalRecord (recorded_by)

### 3.3 Diagrama de Secuencia

**Flujo: "Registrar valor clínico y recibir alerta"**

1. El usuario ingresa un valor clínico en el formulario Vue
2. El frontend envía POST /api/v1/clinical-records con JWT y X-Tenant-ID
3. Laravel aplica TenantMiddleware y JwtAuth
4. ClinicalAlertController valida los datos (patient_id, record_type, value, unit)
5. Si record_type no existe en clinical_ranges, responde 422
6. Si es válido, inserta el registro en la BD
7. ClinicalAlertService.evaluate() consulta el rango correspondiente
8. ClinicalRange.evaluate() compara el valor contra los límites y determina severidad
9. El controlador actualiza la severidad en el registro
10. Responde 201 con el registro completo incluyendo severidad
11. El frontend muestra ToastNotification con el color correspondiente

---

## 4. Explicación de los cambios realizados

### Sprint 1 — Primer avance funcional

**Objetivo:** Crear la estructura base del módulo (backend + frontend) con funcionalidad de registro y evaluación de alertas.

**Archivos creados:**
- 3 migraciones: patients, clinical_ranges, clinical_records
- 3 modelos: Patient, ClinicalRecord, ClinicalRange (con método evaluate())
- 1 servicio: ClinicalAlertService
- 1 controlador: ClinicalAlertController (7 endpoints)
- 1 seeder: ClinicalRangeSeeder (7 parámetros con rangos por defecto)
- 1 store Pinia: clinicalAlerts.js
- 2 páginas Vue: ClinicalAlertsPage, CreateRecordPage
- 2 componentes: ClinicalValueBadge, AlertSummaryCard

**Archivos modificados:**
- routes/api.php: rutas del módulo
- DatabaseSeeder.php: inclusión del ClinicalRangeSeeder
- router/index.js: rutas frontend
- AppLayout.vue: navegación

### Sprint 2 — Ajustes y mejoras

**Objetivo:** Refinar la experiencia de usuario con notificaciones, tests y vista de detalle.

**Archivos creados:**
- ToastNotification.vue: sistema de notificaciones toast animadas
- PatientDetailPage.vue: historial del paciente con timeline
- ClinicalRangeEvaluationTest.php: 13 tests unitarios

**Archivos modificados:**
- CreateRecordPage.vue: integración de ToastNotification
- ClinicalAlertController.php: validación de record_type en clinical_ranges
- ClinicalAlertsPage.vue: filtros de fecha, enlaces a detalle, botón limpiar
- router/index.js: ruta de detalle de paciente

### Sprint 3 — Diagramas UML

**Objetivo:** Documentar el diseño del módulo con diagramas UML.

**Archivos creados:**
- docs/uml-clinical-alerts.md: diagramas en Mermaid (Casos de Uso, Clases, Secuencia)
- docs/word-evidence-clinical-alerts.md: contenido para informe Word

**Archivos modificados:**
- README.md: documentación completa del módulo

---

## 5. Commits principales

| Sprint | Mensaje del commit | Descripción |
|--------|-------------------|-------------|
| **1** | `feat: implement clinical alerts module with alert detection system` | Migraciones, modelos, servicio, controlador, seeders, frontend base |
| **2** | `feat: enhance clinical alerts module with toasts, tests, and patient detail` | Toast notifications, 13 tests, patient detail page, date filters |
| **3** | `docs: add UML diagrams for clinical alerts module` | Diagramas UML, README actualizado, evidencia para Word |

---

## Evidencia de decisión humana

| Sprint | Decisión tomada | Fecha |
|--------|----------------|-------|
| 1 | Aprobación de propuesta técnica completa del módulo | 13/06/2026 |
| 2 | Aprobación para continuar con mejoras del Sprint 2 | 13/06/2026 |
| 3 | Aprobación para generar diagramas UML | 13/06/2026 |
