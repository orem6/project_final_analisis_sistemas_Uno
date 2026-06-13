# Diagramas UML — Módulo Alertas Clínicas Básicas

---

## 1. Diagrama de Casos de Uso

```mermaid
---
title: Casos de Uso - Alertas Clínicas Básicas
---
graph TB
    Medico((Médico))
    Enfermera((Enfermera))
    Admin((Admin))

    subgraph "Sistema de Alertas Clínicas"
        UC1[Registrar Paciente]
        UC2[Registrar Valor Clínico]
        UC3[Ver Alertas Activas]
        UC4[Ver Historial del Paciente]
        UC5[Ver Resumen de Alertas]
        UC6[Configurar Rangos Clínicos]
        UC7[Gestionar Usuarios]
    end

    Medico --> UC1
    Medico --> UC2
    Medico --> UC3
    Medico --> UC4
    Medico --> UC5

    Enfermera --> UC1
    Enfermera --> UC2
    Enfermera --> UC3
    Enfermera --> UC4
    Enfermera --> UC5

    Admin --> UC1
    Admin --> UC2
    Admin --> UC3
    Admin --> UC4
    Admin --> UC5
    Admin --> UC6
    Admin --> UC7

    UC2 -.->|extiende| UC3
```

---

## 2. Diagrama de Clases

```mermaid
---
title: Diagrama de Clases - Alertas Clínicas Básicas
---
classDiagram
    class Tenant {
        +string id
        +string name
        +string slug
        +array data
        +timestamps created_at
        +timestamps updated_at
    }

    class User {
        +int id
        +string tenant_id
        +string name
        +string email
        +string password
        +tenant()
        +getJWTIdentifier()
        +getJWTCustomClaims()
    }

    class Patient {
        +int id
        +string tenant_id
        +string document_number
        +string name
        +string last_name
        +date birth_date
        +string phone
        +string email
        +string gender
        +string address
        +timestamps created_at
        +timestamps updated_at
        +tenant() BelongsTo
        +clinicalRecords() HasMany
        +getFullNameAttribute() string
    }

    class ClinicalRecord {
        +int id
        +string tenant_id
        +int patient_id
        +string record_type
        +decimal value
        +string unit
        +string severity
        +text notes
        +int recorded_by
        +timestamp recorded_at
        +timestamps created_at
        +timestamps updated_at
        +tenant() BelongsTo
        +patient() BelongsTo
        +recorder() BelongsTo
    }

    class ClinicalRange {
        +int id
        +string tenant_id
        +string record_type
        +string label
        +string unit
        +decimal min_value_normal
        +decimal max_value_normal
        +decimal min_value_warning
        +decimal max_value_warning
        +decimal critical_low
        +decimal critical_high
        +timestamps created_at
        +timestamps updated_at
        +tenant() BelongsTo
        +evaluate(float value) string
    }

    class ClinicalAlertService {
        +evaluate(ClinicalRecord record) ClinicalRecord
        +getAlertsSummary(string tenantId) array
        +getActiveAlerts(string tenantId) Collection
    }

    class ClinicalAlertController {
        -ClinicalAlertService alertService
        +patientsIndex(Request) JsonResponse
        +patientsStore(Request) JsonResponse
        +recordsIndex(Request) JsonResponse
        +recordsStore(Request) JsonResponse
        +alertsSummary(Request) JsonResponse
        +activeAlerts(Request) JsonResponse
        +rangesIndex(Request) JsonResponse
    }

    Tenant "1" --> "*" User : has
    Tenant "1" --> "*" Patient : has
    Tenant "1" --> "*" ClinicalRecord : has
    Tenant "1" --> "*" ClinicalRange : has

    Patient "1" --> "*" ClinicalRecord : has

    User "1" --> "*" ClinicalRecord : registers
    ClinicalRecord "*" --> "1" User : recorded_by

    ClinicalAlertController --> ClinicalAlertService : uses
    ClinicalAlertService --> ClinicalRecord : evaluates
    ClinicalAlertService --> ClinicalRange : queries
```

---

## 3. Diagrama de Secuencia

### Flujo: "Registrar valor clínico y recibir alerta"

```mermaid
---
title: Secuencia - Registrar Valor Clínico con Evaluación de Alerta
---
sequenceDiagram
    actor Usuario as Médico / Enfermera
    participant Vue as Frontend Vue 3
    participant API as API REST (Laravel)
    participant Controller as ClinicalAlertController
    participant Service as ClinicalAlertService
    participant DB as Base de Datos

    Usuario->>Vue: Ingresa valor clínico en formulario
    Vue->>Vue: Valida campos locales
    Vue->>API: POST /api/v1/clinical-records
    Note over Vue,API: Headers: Authorization Bearer JWT<br/>X-Tenant-ID: uuid

    API->>API: TenantMiddleware verifica X-Tenant-ID
    API->>API: JwtAuth valida token JWT

    API->>Controller: recordsStore(Request)

    Controller->>Controller: Valida datos (patient_id, record_type, value, unit)

    alt record_type no configurado en clinical_ranges
        Controller-->>API: 422 No hay rango configurado
        API-->>Vue: Error response
        Vue-->>Usuario: Muestra mensaje de error
    else record_type válido
        Controller->>DB: INSERT clinical_records
        DB-->>Controller: Record creado

        Controller->>Service: evaluate(record)

        Service->>DB: SELECT clinical_range WHERE tenant_id AND record_type
        DB-->>Service: Rango encontrado

        Service->>Service: range->evaluate(value)
        Note over Service: Compara valor contra:<br/>critical_low < min_warning < normal < max_warning < critical_high

        alt valor crítico
            Service-->>Controller: severity = 'critical'
        else warning alto/bajo
            Service-->>Controller: severity = 'warning_high'/'warning_low'
        else normal
            Service-->>Controller: severity = 'normal'
        end

        Controller->>DB: UPDATE clinical_records SET severity = ?
        DB-->>Controller: Actualizado

        Controller-->>API: Response 201 { data: record }
        API-->>Vue: JSON con severidad calculada

        alt severity = 'critical'
            Vue->>Vue: Muestra ToastNotification rojo
        else severity != 'normal'
            Vue->>Vue: Muestra ToastNotification amarillo/naranja
        else normal
            Vue->>Vue: Muestra ToastNotification verde
        end

        Vue-->>Usuario: Badge de color en la tabla
    end
```
