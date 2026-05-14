# Sistema de autenticación - SITECRAFT

## Descripción

SITECRAFT utiliza autenticación basada en sesiones PHP nativas y control de acceso por roles (`student`, `teacher`, `admin`).

La autenticación se implementa con una combinación de:

- helpers en `app/helpers/auth.php`,
- controladores PHP en `public/`,
- datos persistidos en las tablas `users`, `roles` y `user_roles`.

---

## Rutas principales

En entorno local con XAMPP, las rutas públicas habituales son:

- `http://localhost/SITECRAFT/public/index.php`
- `http://localhost/SITECRAFT/public/login.php`
- `http://localhost/SITECRAFT/public/logout.php`
- `http://localhost/SITECRAFT/public/student/dashboard.php`
- `http://localhost/SITECRAFT/public/teacher/dashboard.php`
- `http://localhost/SITECRAFT/public/perfil.php`
- `http://localhost/SITECRAFT/public/editar-perfil.php`

Nota: el proyecto calcula automáticamente la base pública con `appBasePath()` y `appUrl()`, por lo que la navegación interna no depende de rutas absolutas escritas a mano.

---

## Archivos implicados

### Helpers

- `app/helpers/auth.php`: login, sesión, control de roles y protección de páginas.
- `app/helpers/index.php`: punto de carga centralizado de helpers.

### Controladores/páginas públicas

- `public/login.php`: procesa el formulario de acceso.
- `public/logout.php`: cierra la sesión actual.
- `public/register.php`: registra alumnos nuevos.
- `public/student/dashboard.php`: panel de alumno.
- `public/teacher/dashboard.php`: panel docente.
- `public/perfil.php`: vista del perfil autenticado.
- `public/editar-perfil.php`: edición de perfil.

### Vistas

- `app/views/auth/login.php`
- `app/views/auth/profile.php`
- `app/views/auth/edit-profile.php`
- `app/views/layouts/header.php`
- `app/views/layouts/header-dashboard.php`

---

## Funciones principales de `app/helpers/auth.php`

| Función | Descripción |
|---|---|
| `appBasePath()` | Calcula la ruta base pública de la aplicación |
| `appUrl($path)` | Construye URLs internas consistentes |
| `startSession($userId, $email, $fullName, $roles)` | Inicia una sesión autenticada |
| `isAuthenticated()` | Comprueba si existe usuario en sesión |
| `getCurrentUser()` | Recupera el usuario almacenado en sesión |
| `hasRole($role)` | Comprueba si el usuario tiene un rol concreto |
| `authenticateUser($email, $password)` | Valida credenciales contra la base de datos |
| `registerUser(...)` | Registra un nuevo alumno con validaciones básicas |
| `logout()` | Destruye la sesión actual |
| `requireAuth($requiredRole = null)` | Protege páginas y puede exigir un rol específico |

---

## Flujo actual de autenticación

1. El usuario accede a una página pública o abre el modal de login desde el header.
2. Envía email y contraseña a `public/login.php`.
3. `authenticateUser()` valida usuario activo, contraseña y roles.
4. Si las credenciales son correctas, `startSession()` guarda los datos en `$_SESSION['user']` y regenera el identificador de sesión.
5. El sistema redirige:
   - a `teacher/dashboard.php` si el usuario es `teacher` o `admin`,
   - a `student/dashboard.php` en caso contrario.
6. Las páginas protegidas usan `requireAuth()` para impedir acceso sin sesión.

---

## Seguridad aplicada

- Uso de `password_hash()` y `password_verify()`.
- Regeneración del identificador de sesión tras login.
- Comprobación de `is_active` en el usuario.
- Control de acceso por rol en paneles y páginas protegidas.
- Consultas preparadas con PDO para evitar SQL injection.
- Escape de salida con `htmlspecialchars()` en vistas.

---

## Registro de usuarios

El registro está implementado para cuentas de alumno.

Validaciones actuales:

- nombre obligatorio,
- email válido,
- contraseña mínima de 6 caracteres,
- confirmación de contraseña,
- email único en `users`.

Al registrarse correctamente, el usuario recibe el rol `student` mediante `user_roles`.

---

## Perfil de usuario

Además del login/logout, el proyecto ya incluye:

- consulta de perfil autenticado,
- sincronización del perfil con la sesión,
- edición de nombre, correo y teléfono,
- validación de email único.

---

## Estado actual

El sistema de autenticación está operativo y cubre:

- login,
- logout,
- registro de alumno,
- dashboards por rol,
- perfil y edición de perfil,
- protección de páginas.
