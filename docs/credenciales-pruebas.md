# Credenciales de prueba (SITECRAFT)

> Entorno local de desarrollo.
> Contraseña para **todas** las cuentas: `password`
> Base pública habitual: `http://localhost/SITECRAFT/public/`

## Alumnos de prueba (curso: Entrenador Personal Nivel I)

| Nombre | Usuario (email) | Contraseña | Estado en curso |
|---|---|---|---|
| Ana López | ana.lopez@sitecraft.local | password | active |
| Carlos Ruiz | carlos.ruiz@sitecraft.local | password | pending_payment |
| Marta Sánchez | marta.sanchez@sitecraft.local | password | completed |
| Diego Fernández | diego.fernandez@sitecraft.local | password | paused |

## Profesores creados

| Nombre | Usuario (email) | Contraseña | Rol |
|---|---|---|---|
| Laura Martín | laura.martin@sitecraft.local | password | teacher |
| James Walker | james.walker@sitecraft.local | password | teacher |
| Claudia Pérez | claudia.perez@sitecraft.local | password | teacher |
| Adrián Gómez | adrian.gomez@sitecraft.local | password | teacher |

## Admin (extra)

| Nombre | Usuario (email) | Contraseña | Rol |
|---|---|---|---|
| Admin SITECRAFT | admin@sitecraft.local | password | admin |

## Scripts a ejecutar (orden)

1. `database/schema.sql`
2. `database/seeds/initial-data.sql`
3. `database/seeds/curso-entrenador-personal.sql`

## Rutas útiles para pruebas

- Login: `http://localhost/SITECRAFT/public/login.php`
- Inicio: `http://localhost/SITECRAFT/public/index.php`
- Panel alumno: `http://localhost/SITECRAFT/public/student/dashboard.php`
- Panel profesor: `http://localhost/SITECRAFT/public/teacher/dashboard.php`
- Perfil: `http://localhost/SITECRAFT/public/perfil.php`
