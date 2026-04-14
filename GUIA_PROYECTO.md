# Guía Completa — Citas Médicas App
**Clínica Nuestra Señora del Rosario**

---

## Índice
1. [Arquitectura del Proyecto](#1-arquitectura-del-proyecto)
2. [Requisitos Previos](#2-requisitos-previos)
3. [Correr el Proyecto con Docker (recomendado)](#3-correr-el-proyecto-con-docker-recomendado)
4. [Correr el Proyecto en Local (sin Docker)](#4-correr-el-proyecto-en-local-sin-docker)
5. [Restauración / Inicialización de la Base de Datos](#5-restauración--inicialización-de-la-base-de-datos)
6. [Usuarios de Prueba](#6-usuarios-de-prueba)
7. [Variables de Entorno](#7-variables-de-entorno)
8. [Comandos Útiles de Referencia](#8-comandos-útiles-de-referencia)

---

## 1. Arquitectura del Proyecto

```
carpeta-padre/                      ← puede llamarse como quieras
├── api-clinica/                    ← Backend Laravel 10 (API REST + JWT)
│   ├── docker-compose.yml          ← Orquestación completa ← EJECUTAR DESDE AQUÍ
│   ├── nginx/
│   │   ├── Dockerfile              ← Multi-stage: compila Angular + configura NGINX
│   │   └── default.conf            ← Configuración de NGINX
│   ├── dockerfile                  ← Imagen PHP-FPM 8.2
│   ├── docker-entrypoint.sh        ← Espera DB, migra, crea storage link
│   └── GUIA_PROYECTO.md            ← Este archivo
└── admin_clinica/                  ← Frontend Angular 16 (panel de administración)
```

> **Importante**: `docker-compose.yml` está dentro de `api-clinica/`.
> Siempre ejecuta los comandos Docker estando **dentro** de esa carpeta.

### Contenedores Docker
| Servicio       | Imagen          | Puerto interno | Puerto expuesto | Descripción                                 |
|----------------|-----------------|---------------|-----------------|---------------------------------------------|
| `db`           | MySQL 8.0       | 3306          | **3316**        | Base de datos con healthcheck               |
| `app`          | PHP-FPM 8.2     | 9000 / 8000   | **8000**        | Laravel + PHP-FPM; corre migraciones al iniciar |
| `nginx`        | NGINX (multi-stage) | 80        | **8080**        | Sirve Angular SPA y hace proxy a Laravel    |
| `phpmyadmin`   | phpMyAdmin      | 80            | **8081**        | UI de administración de la BD               |

### URLs de acceso (Docker)
| URL                          | Descripción                     |
|------------------------------|---------------------------------|
| `http://192.168.100.9:8080/` | Angular SPA (panel admin)       |
| `http://192.168.100.9:8080/api/` | Laravel REST API            |
| `http://192.168.100.9:8081/` | phpMyAdmin                      |
| `127.0.0.1:3316`             | MySQL directo (desde el host)   |

### Stack Tecnológico
- **Frontend**: Angular 16, TypeScript, Bootstrap/plantilla de admin
- **Backend**: Laravel 10, JWT (`tymon/jwt-auth`), Roles y Permisos (`spatie/laravel-permission`)
- **BD**: MySQL 8
- **PDF**: `barryvdh/laravel-dompdf` (reportes y recibos)

### Flujo de autenticación
1. Login → `POST /api/auth/login` → devuelve token JWT + permisos del rol
2. El frontend guarda el token en `localStorage`
3. El `AuthGuard` verifica expiración del JWT en cada ruta protegida
4. El `PermissionInterceptor` maneja respuestas 403

---

## 2. Requisitos Previos

### Para Docker (opción recomendada)
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado y corriendo
- Git

### Para desarrollo local (sin Docker)
- **PHP 8.2** con extensiones: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- **Composer 2**
- **Node.js 18+** y **npm**
- **MySQL 8** corriendo en `127.0.0.1:3306`
- Angular CLI: `npm install -g @angular/cli@16`

---

## 3. Correr el Proyecto con Docker (recomendado)

### Paso 1 — Clonar los repositorios en la misma carpeta padre

```bash
# Ambas carpetas deben quedar una al lado de la otra
git clone <url-del-repo-api>    api-clinica
git clone <url-del-repo-angular> admin_clinica
```

La estructura en disco debe quedar así:
```
carpeta-padre/
├── api-clinica/
└── admin_clinica/
```

### Paso 2 — Entrar a api-clinica (aquí viven docker-compose.yml y nginx/)
```bash
cd api-clinica
```

### Paso 3 — Primera vez: construir y levantar
```bash
docker-compose up -d --build
```

El entrypoint de Laravel hace **todo automáticamente** en este orden:

| Paso | Qué hace |
|------|----------|
| 1 | Instala dependencias con `composer install` |
| 2 | Copia `.env.example` → `.env` si no existe |
| 2 | Genera `APP_KEY` con `php artisan key:generate` si está vacía |
| 2 | Genera `JWT_SECRET` con `php artisan jwt:secret` si está vacío |
| 3 | Espera a que MySQL esté listo (reintentos cada 3s) |
| 4 | Ejecuta `php artisan migrate` |
| 5 | Crea el storage link y ajusta permisos |
| 6 | Inicia PHP-FPM |

El contenedor `nginx` compila Angular en modo producción en paralelo.

> **La primera vez tarda ~3-5 minutos** porque descarga imágenes y compila Angular.

### Paso 4 — Verificar que todo esté corriendo
```bash
docker-compose ps
```
Todos los servicios deben estar en estado `Up`.

### Paso 5 — Ver logs si algo falla
```bash
docker-compose logs -f app      # logs del backend (migraciones, errores PHP)
docker-compose logs -f nginx    # logs de NGINX
docker-compose logs -f db       # logs de MySQL
```

### Paso 6 — Ejecutar los seeders (poblar la BD)
> Ver sección completa en [5. Restauración / Inicialización de la Base de Datos](#5-restauración--inicialización-de-la-base-de-datos)

```bash
docker-compose exec app php artisan db:seed
```

### Levantar sin reconstruir (arranque normal)
```bash
docker-compose up -d
```

### Detener
```bash
docker-compose down           # detiene contenedores (conserva la BD)
docker-compose down -v        # detiene Y BORRA el volumen de MySQL
```

---

## 4. Correr el Proyecto en Local (sin Docker)

### Backend — Laravel

```bash
cd api-clinica
```

**1. Instalar dependencias**
```bash
composer install
```

**2. Copiar y configurar el archivo de entorno**
```bash
cp .env.example .env
```

Editar `.env` con los valores locales:
```env
APP_KEY=          # se genera en el paso 3
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pruevabdcitasmedicas
DB_USERNAME=root
DB_PASSWORD=tu_password_mysql
JWT_SECRET=       # se genera en el paso 4
```

**3. Generar clave de la aplicación**
```bash
php artisan key:generate
```

**4. Generar el JWT secret**
```bash
php artisan jwt:secret
```

**5. Crear la base de datos en MySQL**
```sql
CREATE DATABASE pruevabdcitasmedicas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**6. Ejecutar migraciones**
```bash
php artisan migrate
```

**7. Poblar la BD con seeders** (ver detalle en sección 5)
```bash
php artisan db:seed
```

**8. Crear el storage link**
```bash
php artisan storage:link
```

**9. Iniciar el servidor de desarrollo**
```bash
php artisan serve
# Disponible en: http://127.0.0.1:8000
```

---

### Frontend — Angular

```bash
cd admin_clinica
```

**1. Instalar dependencias**
```bash
npm install
```

**2. Verificar el entorno de desarrollo**
Archivo `src/environments/environment.development.ts`:
```typescript
export const environment = {
  production: false,
  URL_SERVICIOS: 'http://127.0.0.1:8000/api'
};
```

**3. Iniciar el servidor de desarrollo**
```bash
npm start
# Disponible en: http://192.168.100.9:4200
```

**4. Build de producción** (si quieres servir desde NGINX)
```bash
npm run build
# Genera: dist/preclinic-angular/
```

---

## 5. Restauración / Inicialización de la Base de Datos

### Opción A — Seeders (base de datos de prueba completa)

Esta es la opción recomendada para desarrollo. Puebla la BD con datos de prueba realistas.

#### Orden de ejecución de los Seeders

El `DatabaseSeeder` ejecuta los seeders **en este orden exacto** (cada uno depende del anterior):

```
1. PermissionsDemoSeeder   → Permisos, roles y usuarios de staff
2. SpecialitySeeder        → 14 especialidades médicas con precio
3. ScheduleHourSeeder      → 40 slots de horario (08:00 - 18:00, cada 15 min)
4. DoctorSeeder            → 5 doctores con horarios Lun-Vie 08:00-14:00
5. PatientSeeder           → 100 pacientes con usuario vinculado
6. AppointmentSeeder       → 1000 citas con pagos y atenciones
```

#### Qué hace cada seeder en detalle

**1. `PermissionsDemoSeeder`**
- Crea todos los permisos del sistema (40+ permisos: roles, doctores, pacientes, citas, pagos, reportes, ingresos, egresos, seguimientos, portal paciente)
- Crea los roles: `Super-Admin`, `ADMINISTRADOR`, `RECEPCIONISTA`, `ENFERMERO`, `DOCTOR`, `PACIENTE`
- Asigna permisos a cada rol
- Crea los **5 usuarios de staff** iniciales (ver sección 6)

**2. `SpecialitySeeder`**
- Inserta 14 especialidades médicas con su precio en **BOB (bolivianos)**:
  - Medicina General: 80 BOB
  - Odontología: 90 BOB
  - Pediatría: 100 BOB
  - Dermatología: 120 BOB
  - Oftalmología: 130 BOB
  - Anestesiología: 150 BOB
  - Ginecología y Obstetricia: 160 BOB
  - Gastroenterología: 180 BOB
  - Neurología: 220 BOB
  - Traumatología: 200 BOB
  - Anatomía Patológica: 200 BOB
  - Cardiología: 250 BOB
  - Cirugía General: 280 BOB
  - Cirugía Pediátrica: 300 BOB

**3. `ScheduleHourSeeder`**
- Crea 40 slots de horario de 15 minutos cada uno
- Rango: 08:00 a 18:00
- Son los IDs 1–40 que usan los doctores para definir su disponibilidad

**4. `DoctorSeeder`**
- Crea 5 usuarios con rol `DOCTOR`
- Asigna a cada doctor los días Lunes a Viernes
- Asigna los primeros 24 slots (08:00–14:00) como horario disponible
- Contraseña de todos los doctores: `12345678`

**5. `PatientSeeder`**
- Crea 100 pacientes usando `Patient::factory()`
- Por cada paciente crea un `PatientPerson` (acompañante y responsable)
- Crea un usuario vinculado con email `paciente{id}@clinica.com` y contraseña `12345678`
- Asigna el rol `PACIENTE` a cada usuario

**6. `AppointmentSeeder`**
- Crea 1000 citas entre los 5 doctores y los 100 pacientes
- Genera pagos (`AppointmentPay`) y atenciones (`AppointmentAttention`) para las citas completadas

#### Comandos para correr los seeders

**En Docker:**
```bash
# Todos los seeders (desde cero)
docker-compose exec app php artisan db:seed

# Un seeder específico
docker-compose exec app php artisan db:seed --class=PermissionsDemoSeeder
docker-compose exec app php artisan db:seed --class=SpecialitySeeder
docker-compose exec app php artisan db:seed --class=ScheduleHourSeeder
docker-compose exec app php artisan db:seed --class=DoctorSeeder
docker-compose exec app php artisan db:seed --class=PatientSeeder
docker-compose exec app php artisan db:seed --class=AppointmentSeeder
```

**En local:**
```bash
cd api-clinica

php artisan db:seed

# O individual:
php artisan db:seed --class=PermissionsDemoSeeder
php artisan db:seed --class=SpecialitySeeder
# ... etc
```

#### Restauración completa desde cero (borra todo y recrea)

> **ADVERTENCIA**: Esto borra todos los datos existentes.

**En Docker:**
```bash
# Opción 1: bajar volumen y levantar de nuevo (más limpio)
docker-compose down -v
docker-compose up -d --build
# Esperar que arranque (~2 min) y luego:
docker-compose exec app php artisan db:seed

# Opción 2: solo migrar y sembrar sin bajar contenedores
docker-compose exec app php artisan migrate:fresh --seed
```

**En local:**
```bash
cd api-clinica
php artisan migrate:fresh --seed
```

El comando `migrate:fresh` borra todas las tablas, las recrea desde las migraciones y ejecuta automáticamente todos los seeders.

---

### Opción B — Restaurar desde archivo SQL

Si tienes el archivo `bd_citas_medicas_2025.sql` en la raíz del proyecto:

**Desde la terminal (con Docker corriendo):**
```bash
# Importar el SQL al contenedor de MySQL
docker exec -i laravel_db mysql -u admin -padmin123 pruevabdcitasmedicas < bd_citas_medicas_2025.sql
```

**Desde phpMyAdmin:**
1. Abrir `http://192.168.100.9:8081`
2. Usuario: `admin` | Contraseña: `admin123`
3. Seleccionar base de datos `pruevabdcitasmedicas`
4. Ir a **Importar** → seleccionar el archivo `.sql` → **Continuar**

**En local (MySQL directo):**
```bash
mysql -u root -p pruevabdcitasmedicas < bd_citas_medicas_2025.sql
```

---

## 6. Usuarios de Prueba

Después de correr los seeders, estos usuarios están disponibles:

### Staff (panel de administración)
| Rol            | Email                          | Contraseña |
|----------------|-------------------------------|------------|
| Super-Admin    | dquinteros630@gmail.com       | 12345678   |
| ADMINISTRADOR  | admin@clinica.com              | 12345678   |
| RECEPCIONISTA  | recepcionista@clinica.com      | 12345678   |
| ENFERMERO      | enfermero@clinica.com          | 12345678   |
| DOCTOR         | doctor@clinica.com             | 12345678   |

### Doctores (creados por DoctorSeeder)
| Nombre              | Email                        | Contraseña |
|---------------------|------------------------------|------------|
| Dr. Carlos Mendoza  | dr.mendoza@clinica.com       | 12345678   |
| Dra. Ana Rodríguez  | dr.rodriguez@clinica.com     | 12345678   |
| Dr. Luis Fernández  | dr.fernandez@clinica.com     | 12345678   |
| Dra. María Quispe   | dr.quispe@clinica.com        | 12345678   |
| Dr. Jorge Vargas    | dr.vargas@clinica.com        | 12345678   |

### Pacientes
- Emails: `paciente1@clinica.com`, `paciente2@clinica.com`, ..., `paciente100@clinica.com`
- Contraseña: `12345678`

### Permisos por rol
| Rol           | Accesos principales                                                              |
|---------------|----------------------------------------------------------------------------------|
| ADMINISTRADOR | Acceso total a todos los módulos                                                 |
| RECEPCIONISTA | Pacientes, citas, pagos, actividad, seguimientos (ver)                          |
| ENFERMERO     | Pacientes, calendario, ingresos, egresos, seguimientos                          |
| DOCTOR        | Pacientes (ver), calendario, seguimientos, reportes de facturación              |
| PACIENTE      | Solo su propio perfil, citas, historial y seguimientos (portal paciente)        |

---

## 7. Variables de Entorno

### Laravel — `api-clinica/.env`

```env
APP_NAME=Laravel
APP_ENV=local                   # o "production" en Docker
APP_KEY=base64:...              # generado con php artisan key:generate
APP_DEBUG=true
APP_URL=http://192.168.100.9

# Base de datos (local)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pruevabdcitasmedicas
DB_USERNAME=root
DB_PASSWORD=tu_password

# Base de datos (Docker — estos los inyecta docker-compose.yml)
# DB_HOST=laravel_db
# DB_USERNAME=admin
# DB_PASSWORD=admin123

# JWT (generado con php artisan jwt:secret)
JWT_SECRET=tu_jwt_secret
```

### Angular — `admin_clinica/src/environments/`

**Desarrollo local** (`environment.development.ts`):
```typescript
export const environment = {
  production: false,
  URL_SERVICIOS: 'http://127.0.0.1:8000/api'
};
```

**Docker / Producción** (`environment.ts`):
```typescript
export const environment = {
  production: true,
  URL_SERVICIOS: 'http://192.168.100.9:8080/api'
};
```

> Para desplegar en un servidor remoto (EC2, VPS), reemplaza `192.168.100.9` con la IP real del servidor en `environment.ts` y en `docker-compose.yml` (`APP_URL`).

---

## 8. Comandos Útiles de Referencia

### Docker (ejecutar siempre desde dentro de `api-clinica/`)
```bash
docker-compose up -d --build          # primera vez o con cambios de código
docker-compose up -d                  # arrancar sin rebuild
docker-compose down                   # detener (conserva BD)
docker-compose down -v                # detener y borrar la BD
docker-compose ps                     # ver estado de contenedores
docker-compose logs -f app            # logs del backend en tiempo real
docker-compose logs -f nginx          # logs de NGINX
docker-compose logs -f db             # logs de MySQL

# Artisan dentro del contenedor
docker-compose exec app php artisan migrate
docker-compose exec app php artisan migrate:fresh --seed
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan db:seed --class=NombreSeeder
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:list
```

### Laravel (local)
```bash
cd api-clinica
php artisan serve                      # servidor dev en :8000
php artisan migrate                    # ejecutar migraciones pendientes
php artisan migrate:fresh              # borrar tablas y recrear
php artisan migrate:fresh --seed       # borrar, recrear y poblar
php artisan db:seed                    # poblar (requiere tablas existentes)
php artisan db:seed --class=NombreSeeder
php artisan key:generate               # generar APP_KEY
php artisan jwt:secret                 # generar JWT_SECRET
php artisan storage:link               # crear enlace public/storage
php artisan cache:clear
php artisan config:clear
php artisan test                       # correr todos los tests
./vendor/bin/pint                      # formatear código
```

### Angular (local)
```bash
cd admin_clinica
npm install                            # instalar dependencias
npm start                              # servidor dev en :4200
npm run build                          # build de producción → dist/
npm test                               # correr tests (Karma/Jasmine)
npm run lint                           # ESLint
```

---

## Solución de Problemas Comunes

### La BD no arranca / migraciones fallan
```bash
# Ver logs del contenedor db
docker-compose logs db

# Resetear volumen y volver a empezar
docker-compose down -v
docker-compose up -d --build
```

### Error 403 en la API
- Verificar que el usuario tenga el permiso necesario en la BD
- Volver a correr `php artisan db:seed --class=PermissionsDemoSeeder`
- Limpiar caché: `php artisan cache:clear`

### Token JWT expirado / error 401
- Volver a iniciar sesión en el panel
- En desarrollo, verificar que `JWT_SECRET` esté en el `.env`

### Los seeders fallan a la mitad
Si un seeder falla (ej. por datos duplicados), puedes:
```bash
# Limpiar caché de permisos y reintentar
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan db:seed --class=NombreSeederQueFallo
```
O hacer un fresh completo:
```bash
docker-compose exec app php artisan migrate:fresh --seed
```

### Angular no se conecta a la API
- Verificar que `URL_SERVICIOS` en el `environment` correcto apunte a la IP/puerto correcto
- En Docker: debe ser `http://192.168.100.9:8080/api`
- En local: debe ser `http://127.0.0.1:8000/api`
