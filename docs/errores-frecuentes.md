# Errores frecuentes y solución - SITECRAFT

Los problemas más habituales en la plataforma tienen solución rápida. Esta guía cubre los cuatro casos más comunes: una lección que no carga, un recurso que no se visualiza, un curso que no aparece en "Mis cursos" y problemas de acceso o sesión expirada.

---

## No carga una lección

Si al abrir una lección la pantalla queda en blanco, aparece un error o no avanza al contenido, estas son las causas más habituales:

**Causas frecuentes:**
- La lección tiene `is_visible = 0` en la base de datos (está desactivada por el administrador o el profesor).
- La matrícula del alumno se encuentra en estado `pending_payment`, `paused` o `cancelled`. Solo los estados `active` y `completed` permiten acceder al aula (`public/student/curso.php` comprueba esto en la línea de verificación de matrícula).
- El parámetro `?lesson=` de la URL contiene un ID que no pertenece al curso actual.
- Error de conexión a la base de datos en el momento de cargar el módulo.

**Pasos para diagnosticar:**

1. Verificar el estado de la matrícula en la base de datos:
   ```sql
   SELECT enrollment_status
   FROM enrollments
   WHERE user_id = <id_usuario> AND course_id = <id_curso>;
   ```
   El valor debe ser `active` o `completed`.

2. Verificar que la lección y su módulo estén visibles:
   ```sql
   SELECT l.id, l.title, l.is_visible, m.is_visible AS module_visible
   FROM course_lessons l
   JOIN course_modules m ON m.id = l.module_id
   WHERE l.id = <id_leccion>;
   ```
   Ambos campos deben devolver `1`.

3. Si la lección existe y la matrícula es válida, revisar los logs de PHP en `storage/logs/` en busca de excepciones `Throwable` silenciadas.

**Solución:**
- Actualizar el estado de la matrícula a `active` si el pago está confirmado.
- Activar la visibilidad de la lección o el módulo desde el panel de profesor o directamente en la BD con `UPDATE course_lessons SET is_visible = 1 WHERE id = <id>`.
- Si el error persiste, acceder a la lección desde el panel del alumno (`student/dashboard.php`) en lugar de pegar la URL directamente.

---

## No se visualiza un recurso

Los recursos multimedia (vídeos, imágenes, PDFs) se cargan desde rutas relativas bajo `public/assets/uploads/`. Si un recurso no aparece o muestra el icono de imagen rota, las causas más comunes son:

**Causas frecuentes:**
- El archivo físico no existe en la ruta indicada en la columna `url` de `course_media`. Los seeds incluyen rutas de ejemplo que apuntan a archivos de demostración que deben subirse manualmente.
- La ruta almacenada en BD incluye una barra inicial (`/assets/...`) cuando la vista espera una ruta sin ella (`assets/...`), o viceversa. El controlador `app/views/student/curso.php` usa `$resolveMediaPath` para normalizar esto, pero solo funciona si el archivo existe físicamente.
- El tipo `pdf` requiere que el navegador permita iframes o que el PDF esté accesible en la ruta pública. Algunos navegadores bloquean PDFs en iframes de dominios distintos.
- Para vídeos: el formato del archivo no es compatible con el elemento `<video>` nativo (se acepta MP4/H.264; otros formatos requieren transcodificación).

**Pasos para diagnosticar:**

1. Comprobar que el archivo existe en disco:
   ```powershell
   Test-Path "C:\xampp\htdocs\SITECRAFT\public\assets\uploads\courses\<slug-curso>\<nombre-archivo>"
   ```

2. Consultar la ruta almacenada en la BD:
   ```sql
   SELECT id, type, title, url, thumbnail_url
   FROM course_media
   WHERE lesson_id = <id_leccion>
   ORDER BY sort_order;
   ```

3. Abrir la URL del recurso directamente en el navegador:
   ```
   http://localhost/SITECRAFT/assets/uploads/courses/<slug>/<archivo>
   ```
   Si devuelve 404, el archivo no está en disco.

**Solución:**
- Subir el archivo real a `public/assets/uploads/courses/<slug-curso>/`.
- Si la ruta en BD tiene barra inicial incorrecta, corregirla:
  ```sql
  UPDATE course_media
  SET url = TRIM(LEADING '/' FROM url),
      thumbnail_url = TRIM(LEADING '/' FROM thumbnail_url)
  WHERE course_id = <id_curso>;
  ```
- Para PDFs que no se visualizan en el navegador, cambiar el tipo a `link` para que se abran en una pestaña nueva en lugar de intentar incrustarse.

---

## No aparece el curso en "Mis cursos"

El panel del alumno (`student/dashboard.php`) muestra los cursos obtenidos de la tabla `enrollments` cruzada con `courses`. Si un curso no aparece en el listado, las causas habituales son:

**Causas frecuentes:**
- No existe registro en la tabla `enrollments` para ese usuario y curso.
- El curso tiene `is_published = 0` (borrador): la consulta del dashboard incluye un `JOIN` con `courses` y filtra solo publicados.
- La inscripción se realizó pero el pago quedó en estado `pending_payment`. El curso sí aparece en el panel, pero con la etiqueta "Pago pendiente" y sin acceso al aula.
- El usuario inició sesión con un email distinto al que está matriculado.

**Pasos para diagnosticar:**

1. Verificar si existe la matrícula:
   ```sql
   SELECT e.id, e.enrollment_status, e.started_at, c.title, c.is_published
   FROM enrollments e
   JOIN courses c ON c.id = e.course_id
   WHERE e.user_id = <id_usuario>;
   ```

2. Si la matrícula no existe y el usuario pagó, revisar pagos enlazados a matrículas:
   ```sql
   SELECT p.*, e.user_id, e.course_id
   FROM payments p
   JOIN enrollments e ON e.id = p.enrollment_id
   WHERE e.user_id = <id_usuario>
   ORDER BY p.created_at DESC
   LIMIT 5;
   ```

3. Comprobar que el `user_id` de la sesión activa es el correcto:
   ```sql
   SELECT id, full_name, email FROM users WHERE id = <id_usuario>;
   ```

**Solución:**
- Si falta la matrícula pero el pago está confirmado, insertarla manualmente:
  ```sql
  INSERT INTO enrollments (user_id, course_id, enrollment_status, started_at, ends_at)
  VALUES (
      <id_usuario>,
      <id_curso>,
      'active',
      NOW(),
      DATE_ADD(NOW(), INTERVAL 60 DAY)
  );
  ```
- Si la matrícula existe con `pending_payment`, actualizar el estado una vez confirmado el pago:
  ```sql
  UPDATE enrollments
  SET enrollment_status = 'active',
      started_at = NOW(),
      ends_at = DATE_ADD(NOW(), INTERVAL 60 DAY)
  WHERE user_id = <id_usuario> AND course_id = <id_curso>;
  ```
- Si el curso no está publicado, activarlo:
  ```sql
  UPDATE courses SET is_published = 1 WHERE id = <id_curso>;
  ```

---

## Problemas de acceso o sesión expirada

La autenticación en SITECRAFT usa sesiones PHP nativas gestionadas en `app/helpers/auth.php`. Los problemas de acceso más comunes son:

**Causas frecuentes:**
- La sesión PHP expiró por inactividad (el tiempo de vida depende de la configuración `session.gc_maxlifetime` de PHP, por defecto 1440 segundos en XAMPP).
- El archivo de sesión fue eliminado del directorio `storage/sessions/` o del directorio temporal de PHP (`C:\xampp\tmp\`).
- El usuario cambió su contraseña o fue desactivado (`is_active = 0`) mientras tenía sesión abierta. La sesión sigue activa hasta que caduca, pero `authenticateUser()` rechazará el siguiente intento de login.
- Cookies bloqueadas por el navegador o configuración de `SameSite`/`Secure` incompatible con HTTP en localhost.
- Acceso desde dos pestañas o dispositivos distintos puede provocar conflictos de sesión si `session_regenerate_id(true)` elimina la sesión anterior.

**Pasos para diagnosticar:**

1. Comprobar si la sesión existe y tiene datos válidos añadiendo temporalmente en cualquier página protegida:
   ```php
   var_dump($_SESSION);
   ```
   Si devuelve un array vacío o sin la clave `user`, la sesión caducó o fue destruida.

2. Verificar que el usuario sigue activo:
   ```sql
   SELECT id, full_name, email, is_active FROM users WHERE email = '<email>';
   ```

3. Revisar el log de PHP para excepciones relacionadas con la sesión:
   ```
   storage/logs/
   C:\xampp\php\logs\php_error_log
   ```

4. En Chrome DevTools → Application → Cookies: comprobar que existe la cookie `PHPSESSID` y que no está marcada como expirada.

**Solución:**

| Problema | Solución |
|---|---|
| Sesión expirada | Volver a iniciar sesión en `/public/login.php` |
| Usuario desactivado | Activar en BD: `UPDATE users SET is_active = 1 WHERE email = '<email>'` |
| Cookie no se guarda | En localhost con XAMPP, asegurarse de acceder por `http://localhost/` y no por `http://127.0.0.1/` |
| Redirige siempre a login | Verificar que `requireAuth()` esté llamando a `startSession()` correctamente y que `$_SESSION['user']['id']` existe |
| Contraseña incorrecta | Regenerar hash: `password_hash('nueva_contraseña', PASSWORD_DEFAULT)` e insertar en `users.password_hash` |

**Función clave en `app/helpers/auth.php`:**

```php
// Comprobar si el usuario está autenticado
isAuthenticated();   // devuelve bool

// Obtener datos del usuario en sesión
getCurrentUser();    // devuelve array|null

// Proteger una página (redirige a login si no hay sesión)
requireAuth();

// Cerrar sesión y limpiar
logout();
```

**Recomendación:** si el problema de sesión es recurrente en XAMPP, aumentar el tiempo de vida en `C:\xampp\php\php.ini`:
```ini
session.gc_maxlifetime = 7200
session.cookie_lifetime = 7200
```
Reiniciar Apache tras el cambio.

---

## Resumen rápido

| Síntoma | Primera acción |
|---|---|
| Lección en blanco o error al cargar | Verificar `enrollment_status` = `active` y `is_visible` = `1` en la lección |
| Imagen/vídeo/PDF no aparece | Comprobar que el archivo existe en `public/assets/uploads/` |
| Curso no aparece en "Mis cursos" | Verificar que existe registro en `enrollments` para ese `user_id` |
| Redirige a login o "sesión expirada" | Volver a iniciar sesión; si persiste, comprobar `is_active` del usuario |
