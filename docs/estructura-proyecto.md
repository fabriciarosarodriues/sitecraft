# Estructura actual del proyecto - SITECRAFT

## Enfoque general

El proyecto sigue una estructura PHP organizada por capas ligeras:

- `public/` contiene puntos de entrada y páginas ejecutables,
- `app/views/` contiene la presentación,
- `app/helpers/` centraliza utilidades compartidas,
- `app/controllers/`, `app/models/` y `app/services/` agrupan lógica reutilizable,
- `api/` contiene endpoints AJAX o JSON,
- `database/` conserva esquema y seeds.

No existe un framework MVC completo; se trata de una arquitectura propia, sencilla y pragmática.

---

## Estructura real resumida

```text
SITECRAFT/
├─ api/
│  ├─ auth/
│  ├─ chatbot/
│  ├─ cursos/
│  ├─ notificaciones/
│  ├─ nutricion/
│  └─ pagos/
├─ app/
│  ├─ config/
│  │  └─ database.php
│  ├─ controllers/
│  │  └─ NotificationController.php
│  ├─ helpers/
│  │  ├─ auth.php
│  │  └─ index.php
│  ├─ models/
│  │  └─ Notification.php
│  ├─ services/
│  │  ├─ NotificationService.php
│  │  └─ chatbot/
│  └─ views/
│     ├─ auth/
│     ├─ courses/
│     ├─ home/
│     ├─ layouts/
│     ├─ student/
│     ├─ support/
│     └─ teacher/
├─ database/
│  ├─ migrations/
│  ├─ procedures/
│  ├─ seeds/
│  └─ schema.sql
├─ docs/
├─ public/
│  ├─ index.php
│  ├─ cursos.php
│  ├─ curso.php
│  ├─ login.php
│  ├─ logout.php
│  ├─ register.php
│  ├─ inscribirse.php
│  ├─ ayuda.php
│  ├─ perfil.php
│  ├─ editar-perfil.php
│  ├─ profesores.php
│  ├─ student/
│  │  ├─ dashboard.php
│  │  ├─ curso.php
│  │  ├─ chatbot.php
│  │  └─ notificaciones.php
│  ├─ teacher/
│  │  ├─ dashboard.php
│  │  └─ curso.php
│  └─ assets/
│     ├─ css/
│     ├─ icons/
│     ├─ img/
│     ├─ js/
│     └─ uploads/
├─ queue/
├─ routes/
├─ storage/
├─ tests/
└─ workers/
```

---

## Puntos de entrada principales

### Público general

- `public/index.php`: landing y cursos destacados.
- `public/cursos.php`: catálogo completo.
- `public/curso.php`: detalle de curso e inscripción.
- `public/profesores.php`: directorio de profesores.
- `public/ayuda.php`: formulario de soporte.

### Autenticación y perfil

- `public/login.php`
- `public/logout.php`
- `public/register.php`
- `public/perfil.php`
- `public/editar-perfil.php`

### Alumno

- `public/student/dashboard.php`
- `public/student/curso.php`
- `public/student/chatbot.php`
- `public/student/notificaciones.php`

### Profesor

- `public/teacher/dashboard.php`
- `public/teacher/curso.php`

---

## Capas principales

### `app/config`

Contiene la conexión a base de datos (`database.php`).

### `app/helpers`

Contiene helpers reutilizables de autenticación, sesiones y generación de URLs.

### `app/controllers`

Actualmente el controlador con mayor uso explícito es `NotificationController.php`.

### `app/models`

El modelo `Notification.php` encapsula consultas y operaciones sobre la tabla `notifications`.

### `app/services`

`NotificationService.php` agrupa la creación de notificaciones automáticas. La carpeta `services/chatbot/` existe como base para ampliaciones futuras.

### `app/views`

Organiza las vistas por dominio funcional:

- `auth/`
- `courses/`
- `home/`
- `layouts/`
- `student/`
- `support/`
- `teacher/`

---

## Estado actual del chatbot

El chatbot operativo del alumno se implementa actualmente mediante:

- `public/student/chatbot.php`
- `app/views/student/chatbot.php`
- `public/assets/css/pages/student/chatbot.css`
- tablas `chatbot_configs` y `chat_messages`

La carpeta `app/services/chatbot/` existe como base estructural, pero el comportamiento del chatbot activo se resuelve hoy desde el controlador/página pública del alumno apoyándose en la base de datos del curso.

---

## Estado actual del proyecto

### Implementado

- autenticación y control por roles,
- dashboards de alumno y profesor,
- detalle de curso e inscripción,
- aula virtual del alumno,
- chatbot por curso,
- notificaciones del alumno,
- perfil y edición de perfil,
- formulario de ayuda,
- sistema de progreso y privilegios,
- documentación funcional y técnica.

### Observación

Algunas carpetas (`api/auth`, `api/chatbot`, `services/chatbot`, `routes`, `workers`) están preparadas para crecimiento futuro y no representan todavía una implementación completa de todos los componentes que su nombre podría sugerir.
- Página de catálogo de cursos (`cursos.php`)
- Página de detalle de curso con modal de inscripción
- Inscripción a cursos (gratuitos y de pago) con validaciones
- Dashboard de profesor con listado de cursos
- Panel de administración de curso para profesor (lista de alumnos, progreso, etc.)
- Panel de alumno (básico)

### 🔄 En Progreso
- Implementación completa de pages de estudiante (mis cursos, pagos, insignias, progreso, nutrición)
- Sistema de pagos integrado
- Chatbot con módulos por curso
- Admin panel
- Sistema de notificaciones (email, WhatsApp)

### 📋 Próximas Tareas
1. Crear/completar dashboard del estudiante
2. Crear página de pagos y mis cursos del estudiante
3. Crear vista de contenido del curso (lecciones, módulos)
4. Sistema de badges/insignias
5. Implementar chatbot por curso
6. Panel de administrador
7. Sistema de notificaciones
8. Vistas específicas por tipo de curso (fitness, idiomas, programación)

---

---

## Siguiente paso sugerido

Para continuar el desarrollo, se recomienda:

1. **Dashboard completo del estudiante**: Mostrar cursos activos, progreso, racha y badges
2. **Vista de curso pagado**: Acceso al contenido del curso, lecciones, módulos y recursos
3. **Sistema de pagos**: Integrar gateway de pago (Stripe, PayPal, etc.) para cursos de pago
4. **Chatbot integrado**: Implementar `ChatbotService` para asistencia dentro de cursos
5. **Admin panel**: CRUD de usuarios, cursos, pagos y configuración global

---

## Descripción breve por página (funcional)

### Públicas

- `public/index.php`: Landing principal, barra de navegación, acceso/login rápido y listado general de cursos.
- `public/login.php`: Inicio de sesión con redirección inteligente según rol (alumno, profesor o admin).
- `public/register.php`: Registro de nuevos usuarios con validaciones (email único, contraseña min 6 caracteres).
- `public/logout.php`: Cierre de sesión y limpieza.
- `public/curso.php`: Vista pública del detalle de un curso (descripción, profesor, contenido y botón de inscripción con modal).
- `public/cursos.php`: Página de catálogo de todos los cursos con listado completo.
- `public/inscribirse.php`: Endpoint POST que procesa la inscripción del usuario a un curso (crea enrollment y payment si aplica).

### Home

- `app/views/home/landing.php`: Página principal con hero, información de la plataforma y destacados de cursos.
- `app/views/courses/catalog.php`: Catálogo completo de cursos con descripción, precio y acceso a detalle.

### Auth

- `app/views/auth/login.php`: Formulario de login en modal de Bootstrap (integrado en header).
- `app/views/auth/register.php`: Formulario de registro en modal de Bootstrap (integrado en header).
- **Modales en header.php**: Login y registro accesibles desde todas las páginas via modal de Bootstrap.

### Alumno

- `app/views/student/dashboard.php`: Resumen del alumno (cursos activos, progreso, racha e insignias recientes).
- `app/views/student/mis-cursos.php`: Listado de cursos inscritos con estado (activo, pendiente de pago, finalizado).
- `app/views/student/curso-detalle.php`: Acceso al contenido del curso inscrito, progreso y requisitos de desbloqueo.
- `app/views/student/pagos.php`: Gestión de pagos pendientes e historial con enlace de pago.
- `app/views/student/insignias.php`: Muro de insignias obtenidas y criterios de logro.
- `app/views/student/progreso.php`: Seguimiento de asistencia, horas dedicadas y rachas.
- `app/views/student/nutricion.php`: Registro diario de comidas/sensaciones y consulta al chatbot nutricional (si aplica).

### Documentación funcional añadida

- `docs/especificaciones-logros-privilegios.md`: Reglas funcionales del sistema de racha, tiempo dedicado y privilegios desbloqueables del alumno, reutilizable para manual de usuario.

### Profesor

- `public/teacher/dashboard.php`: Panel del profesor con resumen de cursos, estadísticas (alumnos activos, completados) y lista de cursos.
- `public/teacher/curso.php`: Controlador que carga el panel de administración de un curso específico.
- `app/views/teacher/dashboard.php`: Vista con mis cursos, estadísticas generales y botón para administrar cada curso.
- `app/views/teacher/curso.php`: Vista de administración de curso con lista de alumnos matriculados, estado, progreso y fechas.

### Admin

- `app/views/admin/dashboard.php`: Panel global con KPIs de usuarios, cursos, pagos y actividad.
- `app/views/admin/usuarios.php`: Alta/edición/bloqueo de usuarios y gestión de roles/permisos.
- `app/views/admin/cursos.php`: Administración de cursos (crear, editar, publicar, archivar).
- `app/views/admin/pagos.php`: Auditoría y gestión administrativa de pagos.
- `app/views/admin/insignias.php`: Configuración de insignias, requisitos y asignación automática/manual.
- `app/views/admin/cola-mensajes.php`: Monitor de cola de notificaciones (email/WhatsApp), reintentos y fallos.
- `app/views/admin/configuracion.php`: Parámetros globales del sistema (chatbot, notificaciones, integraciones).

### Cursos (módulos específicos)

- `app/views/courses/detail.php`: Página pública de detalle de curso con hero, programa, profesores, requisitos e inscripción via modal.
- `app/views/courses/catalog.php`: Catálogo de cursos con lista de todos los cursos disponibles.
- `app/views/courses/common/*`: Vistas reutilizables para cualquier curso (estructura base, tabs y componentes comunes).
- `app/views/courses/fitness/*`: Páginas específicas de entrenamiento con soporte de chatbot especializado.
- `app/views/courses/idiomas/*`: Páginas específicas de práctica de idioma y recursos.
- `app/views/courses/programacion/*`: Páginas específicas para retos, entregas y seguimiento técnico.

### API de chatbot (soporte de páginas)

- `api/chatbot/send.php`: Recibe mensajes del chat y responde usando el módulo correspondiente al curso.
- `api/chatbot/welcome.php`: Devuelve mensaje de bienvenida dinámico al iniciar sesión.
- `api/chatbot/course-context.php`: Entrega contexto/permisos del curso para personalizar respuestas del bot.

---

## Flujo de Inscripción

1. **Usuario accede a un curso** (`public/curso.php?slug=...`)
2. **Haz clic en "Inscribirme ahora"** → Se abre modal `#enrollModal`
3. **Modal valida autenticación:**
   - Si no está autenticado → Muestra botones para Login/Registro
   - Si está autenticado → Muestra formulario de inscripción
4. **Formulario de inscripción:**
   - Muestra resumen del curso
   - Si es de pago: Pide seleccionar método de pago (tarjeta, transferencia, PayPal, Bizum)
   - Checkbox de términos y condiciones
5. **POST a `public/inscribirse.php`:**
   - Valida datos y usuario autenticado
   - Verifica que no esté ya inscrito
   - Crea entry en `enrollments` (estado: 'active' si gratis, 'pending_payment' si pago)
   - Si es pago: Crea entry en `payments` con estado 'pending'
   - Redirige con mensaje de éxito o error

---

## Estrategia de CSS por página

Sí, es totalmente válido que cada página tenga su propio CSS. Para mantener el proyecto escalable, conviene usar un enfoque mixto:

1. **Base global** para estilos compartidos.
2. **CSS por página** para estilos específicos de cada vista.
3. **CSS por módulo de curso** cuando el curso tenga UI especial.

### Estructura recomendada de estilos

```text
public/assets/css/
├─ base/
│  ├─ bootstrap.min.css      ← Bootstrap 5.3.3 local
│  ├─ layout.css              ← Estilos de layout global
│  └─ header.css              ← Estilos del header y modales auth
├─ pages/
│  ├─ home/
│  │  ├─ landing.css
│  │  └─ catalog.css
│  ├─ courses/
│  │  └─ detail.css
│  ├─ student/
│  │  ├─ dashboard.css
│  │  └─ (más estilos de alumno)
│  └─ teacher/
│     ├─ dashboard.css
│     └─ curso.css
├─ icons/
│  ├─ bootstrap-icons.css    ← Iconos Bootstrap Icons local
│  ├─ bootstrap-icons.woff
│  └─ bootstrap-icons.woff2
└─ style.css
```

### Regla práctica de carga

Todos los archivos PHP incluyen en el header:
- `app/helpers/index.php` que a su vez incluye `app/helpers/auth.php`
- Bootstrap CSS local + Bootstrap Icons local + layout.css + header.css
- CSS específico de la página actual (pasado en array `$pageCss`)
- Bootstrap JS local en el footer

Convención:
- `startSession()` en cada página para inicializar sesión e inyectar `$user` si autenticado
- Funcion `requireAuth()` en páginas protegidas para redirigir no autenticados
- Función `hasRole()` para validar permisos específicos
- `appUrl()` para todas las URLs internas (maneja subfolder de XAMPP automáticamente)
