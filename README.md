# Sistema de Alertas Clínicas Básicas — Evaluación Final Análisis de Sistemas I

Proyecto **Laravel 12 + Vue 3 (Vite)** con **JWT**, **Spatie Laravel Permission** y **Stancl Tenancy** (tenant identificado por cabecera `X-Tenant-ID`).

**Módulo implementado:** Alertas Clínicas Básicas (Módulo #18)
**Estudiante:** [Tu nombre]
**Repositorio fork basado en:** https://github.com/rortizs/project_final_analisis_sistemas_Uno.git

---

## Arquitectura construida

La aplicación sigue un modelo **SPA + API REST**: el navegador carga una única vista Blade que monta Vue; el backend expone JSON bajo `/api/v1`.

### Vista general

| Capa | Tecnología | Para qué sirve |
|------|------------|----------------|
| **Backend / API** | Laravel 12 | Punto único de negocio, persistencia, seguridad y contratos HTTP JSON. |
| **Autenticación API** | `tymon/jwt-auth` | Emite y valida tokens JWT en el guard `api`; no usa sesiones para el API. |
| **Autorización (RBAC)** | `spatie/laravel-permission` | Roles y permisos sobre el modelo `User` (guard `api`). |
| **Multitenancy base** | `stancl/tenancy` + tabla `tenants` | Modelo `Tenant` y columna `tenant_id` en usuarios. El tenant activo se **indica en cada petición** con `X-Tenant-ID` (sin bases de datos separadas en esta fase). |
| **Middleware propio** | `TenantMiddleware`, `JwtAuth` | `TenantMiddleware` resuelve y valida el tenant por cabecera; `JwtAuth` protege rutas con JWT y coherencia tenant–token. |
| **Frontend** | Vue 3 + Vue Router + Pinia | SPA: rutas del lado cliente, estado global (p. ej. sesión / token) y pantallas como login. |
| **Build frontend** | Vite 7 + `@vitejs/plugin-vue` | Empaqueta JS/CSS; alias `@` apunta a `resources/js`. |
| **Cliente HTTP** | Axios (`resources/js/plugins/axios.js`) | Llama al API con `Authorization: Bearer` y `X-Tenant-ID` según lo guardado en `localStorage`. |
| **Vista shell** | `resources/views/app.blade.php` | Inyecta el bundle Vite y el `<div id="app">` donde Vue se monta. |
| **Rutas web** | `routes/web.php` | Cualquier ruta devuelve la misma SPA (fallback) para que Vue Router maneje `/`, `/login`, etc. |

### Flujo típico de una petición

1. El usuario (o el formulario de login) fija el **ID del tenant**; Axios envía `X-Tenant-ID` y, si hay sesión, el **JWT** en `Authorization`.
2. Laravel aplica `TenantMiddleware` donde corresponda: si el tenant no existe, responde 404 JSON.
3. En rutas protegidas, `jwt.auth` valida el token; opcionalmente se compara el tenant del header con el del usuario del token.
4. Las respuestas del API son siempre **JSON**.

### Estructura relevante en el repo

```
app/Http/Controllers/Api/V1/AuthController.php   # registro, login, me, refresh, logout
app/Http/Middleware/TenantMiddleware.php         # cabecera X-Tenant-ID
app/Http/Middleware/JwtAuth.php                  # JWT + coherencia tenant
app/Models/User.php                              # JWT + HasRoles + tenant_id
app/Models/Tenant.php                            # modelo Stancl / tabla tenants
resources/js/                                    # Vue: router, stores, páginas, Axios
routes/api.php                                   # rutas bajo prefijo api/v1 (ver bootstrap/app.php)
```

---

## Qué se necesita para correr el proyecto

### Software instalado en tu máquina

| Requisito | Uso |
|-----------|-----|
| **PHP ≥ 8.2** | Ejecutar Laravel y Composer scripts (`artisan`, migraciones). |
| **Composer ≥ 2.x** | Instalar dependencias PHP (`vendor/`). |
| **Node.js ≥ 20** y **npm** | Instalar dependencias JS y ejecutar Vite (`npm run dev` / `npm run build`). |
| **Extensiones PHP habituales** | `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath` (según tu stack). |
| **Base de datos** | **SQLite** (rápido en desarrollo, archivo `database/database.sqlite`) o **MySQL 8** en entornos más cercanos a producción. |

### Variables de entorno imprescindibles

Tras copiar `.env.example` a `.env`:

- **`APP_KEY`** — `php artisan key:generate`
- **`JWT_SECRET`** — `php artisan jwt:secret`
- **Conexión a BD** — según elijas SQLite o MySQL en `.env`
- **`VITE_API_URL`** — URL base del API que usará el frontend en desarrollo (p. ej. `http://localhost:8000/api/v1`) si el navegador sirve la SPA desde otro puerto (Vite).

Sin PHP/Composer/Node o sin BD configurada, el proyecto no podrá migrar ni compilar el frontend.

---

## Instalación y ejecución

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

Configura la base de datos en `.env` (SQLite o MySQL). Luego:

```bash
php artisan migrate
npm install
npm run dev
```

En **otra terminal**, el servidor HTTP de Laravel:

```bash
php artisan serve
```

Abre el frontend según la URL que muestre Vite (típicamente `http://localhost:5173`) y asegúrate de que `VITE_API_URL` apunte al backend (`php artisan serve` suele ser `http://127.0.0.1:8000`).

### Variables `.env` más usadas

| Variable | Descripción |
|----------|-------------|
| `APP_URL` | URL pública del backend (p. ej. `http://localhost:8000`). |
| `FRONTEND_URL` | URL del frontend en desarrollo (referencia / CORS si aplica). |
| `JWT_SECRET` | Secreto de firma JWT (generado con `jwt:secret`). |
| `JWT_TTL` | Minutos de vida del access token (por defecto 60). |
| `VITE_API_URL` | Base URL del API para Axios desde Vite. |

## API (`/api/v1`)

Todas las rutas del API requieren la cabecera **`X-Tenant-ID`** (UUID del tenant).

| Método | Ruta | Auth |
|--------|------|------|
| POST | `/auth/register` | No (devuelve JWT al registrar) |
| POST | `/auth/login` | No |
| GET | `/auth/me` | Bearer JWT |
| POST | `/auth/refresh` | Middleware `jwt.refresh` (renovación con ventana de refresh) |
| POST | `/auth/logout` | Bearer JWT |

Respuestas siempre en **JSON**.

---

## Validación recomendada

```bash
php artisan route:list --path=api
php artisan config:clear
npm run build
php artisan test
```

---

## Módulo Implementado: Alertas Clínicas Básicas

### Descripción

El módulo **Alertas Clínicas Básicas** permite registrar valores clínicos de pacientes y visualizar alertas visuales cuando dichos valores se encuentran fuera de los rangos normales. Soporta 7 parámetros clínicos con evaluación automática de severidad.

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

### Niveles de severidad y colores

| Severidad | Color | Significado |
|-----------|-------|-------------|
| `normal` | Verde | Valor dentro de rango |
| `warning_low` | Amarillo | Ligeramente bajo |
| `warning_high` | Naranja | Ligeramente alto |
| `critical` | Rojo (con animación) | Requiere atención inmediata |

### API — Endpoints del módulo

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/patients` | Listar pacientes |
| POST | `/patients` | Crear paciente |
| GET | `/clinical-records` | Listar registros clínicos (filtros: patient_id, record_type, severity, from, to) |
| POST | `/clinical-records` | Crear registro clínico (evalúa alerta automáticamente) |
| GET | `/clinical-records/alerts/summary` | Resumen de alertas activas por severidad |
| GET | `/clinical-records/alerts/active` | Lista de alertas activas |
| GET | `/clinical-ranges` | Listar rangos clínicos configurados |

### Frontend — Rutas del módulo

| Ruta | Página | Descripción |
|------|--------|-------------|
| `/clinical-alerts` | ClinicalAlertsPage | Listado de registros con filtros, tabla y resumen de alertas |
| `/clinical-alerts/create` | CreateRecordPage | Formulario para nuevo registro clínico |
| `/clinical-alerts/patient/:id` | PatientDetailPage | Historial clínico del paciente con timeline |

### Estructura del módulo

```
resources/js/modules/clinical-alerts/
├── components/
│   ├── AlertSummaryCard.vue        # Tarjetas resumen de alertas
│   ├── ClinicalValueBadge.vue      # Badge con color según severidad
│   └── ToastNotification.vue       # Notificaciones toast animadas
├── pages/
│   ├── ClinicalAlertsPage.vue      # Página principal
│   ├── CreateRecordPage.vue        # Formulario de registro
│   └── PatientDetailPage.vue       # Historial del paciente
└── stores/
    └── clinicalAlerts.js           # Store Pinia

app/
├── Http/Controllers/Api/V1/
│   └── ClinicalAlertController.php # Controlador con 7 endpoints
├── Models/
│   ├── Patient.php                 # Modelo de paciente
│   ├── ClinicalRecord.php          # Modelo de registro clínico
│   └── ClinicalRange.php           # Modelo de rango con método evaluate()
└── Services/
    └── ClinicalAlertService.php    # Lógica de evaluación de alertas

database/
├── migrations/
│   ├── 2026_06_13_080514_create_patients_table.php
│   ├── 2026_06_13_080515_create_clinical_ranges_table.php
│   └── 2026_06_13_080516_create_clinical_records_table.php
└── seeders/
    └── ClinicalRangeSeeder.php     # 7 rangos por defecto
```

### Commits principales

| Sprint | Mensaje |
|--------|---------|
| 1 | `feat: implement clinical alerts module with alert detection system` |
| 2 | `feat: enhance clinical alerts module with toasts, tests, and patient detail` |
| 3 | `docs: add UML diagrams for clinical alerts module` |

### Cómo probar el módulo

1. Iniciar servidores:
   ```bash
   php artisan serve
   npm run dev
   ```

2. Ir a `http://localhost:5173/clinical-alerts`

3. Ingresar con credenciales de prueba:
   - Tenant ID: `00000000-0000-4000-8000-000000000001`
   - Email: `admin@hospital.com` / `medico@hospital.com` / `enfermera@hospital.com`
   - Contraseña: `password`

4. Crear un paciente y luego registrar valores clínicos para ver las alertas en acción.

### Ejecutar tests

```bash
php artisan test
```

Incluye 13 tests unitarios para la lógica de evaluación de rangos clínicos.

---

## Entrega esperada

El estudiante debe trabajar sobre su propio fork del repositorio y entregar en Canvas el enlace al repositorio forkeado, junto con una breve descripción del módulo implementado y los commits principales que evidencian su avance.
