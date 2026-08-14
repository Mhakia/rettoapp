# Te Reto App

Plataforma web gamificada de inclusión educativa para instituciones colombianas. Estudiantes, profesores y acudientes completan **retos** orientados a cada rol como vehículo para trabajar inclusión educativa y bienestar socioemocional.

Es multi-tenant sobre una sola base de datos: cada institución está aislada por `institution_id`, mientras que `super_admin`, `manager` y `pedagogue` operan a nivel de plataforma. `student`, `teacher` y `guardian` tienen **identidad portable**: no pertenecen de forma fija a una institución, lo que evita cuentas duplicadas y conserva su historial al transferirse entre colegios.

## Stack

- Laravel 13 · PHP 8.3 · PostgreSQL 16
- Livewire 4 + Flux UI 2 · Tailwind CSS v4
- Laravel Fortify (auth) + Sanctum (API futura)
- Laravel Reverb (tiempo real) · Predis (cache, colas, broadcasting)
- Spatie Laravel-Permission (roles/permisos) · Spatie Laravel-Activitylog (auditoría)
- Spatie Browsershot (PDF) · Maatwebsite/Excel (exportes)
- Laravel Cashier (suscripción por institución) · League Flysystem S3 (adjuntos/evidencias)

## Roles

| Rol | Alcance |
|---|---|
| `super_admin` | Plataforma completa: crea instituciones, `manager`s y `pedagogue`s, ve estadísticas globales. |
| `manager` | Da de alta instituciones y sus `institution_admin`. |
| `pedagogue` | Crea y administra el catálogo de retos; ve estadísticas de retos. |
| `institution_admin` | Administra su institución: matrícula, retiro, boletines, suscripción. |
| `teacher` | Gestiona sus estudiantes, verifica retos de estudiantes, completa retos propios. |
| `guardian` | Ve el progreso de sus estudiantes vinculados (puede tener hijos en varias instituciones). |
| `student` | Completa retos orientados a su rol. |

## Requisitos

- PHP 8.3+ con extensiones `pdo_pgsql`, `pgsql`, `redis`
- PostgreSQL 16
- Redis
- Node.js 18+ / npm
- Composer

## Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configura en `.env` la conexión a PostgreSQL (`DB_*`), Redis (`REDIS_*`, `QUEUE_CONNECTION=redis`, `CACHE_STORE=redis`) y, si vas a sembrar los usuarios de plataforma, `TEAM_PASSWORD`.

```bash
php artisan migrate --seed
npm install
npm run build
```

Para desarrollo:

```bash
npm run dev
php artisan queue:listen
php artisan reverb:start
```

## Módulos principales

- **Actores y matrícula**: alta/transferencia/retiro de `student`/`teacher`/`guardian` por documento, buscando en toda la plataforma antes de crear cuentas duplicadas (`app/Livewire/Actors`).
- **Instituciones**: listado y administración a nivel de plataforma, con acceso a estudiantes/profesores/grupos de cada una (`app/Livewire/Institutions`).
- **Retos (Challenges)**: catálogo, restricción por institución, envío de evidencia, verificación docente y estadísticas agregadas (`app/Livewire/Challenges`).
- **Boletines**: comando programado (`bulletins:send`) que encola un job por institución para generar PDF (Browsershot), subirlo a S3 y enviar un correo consolidado por acudiente.
- **Suscripciones**: Laravel Cashier sobre el modelo `Institution` (el cliente es el colegio, no el usuario admin).

## Comandos útiles

```bash
php artisan test              # suite de pruebas
./vendor/bin/pint              # estilo de código
php artisan migrate:fresh --seed  # reconstruir BD con datos de demo
```

Los datos de demostración (`DemoDataSeeder`) solo corren en entornos `local`/`dev`.
