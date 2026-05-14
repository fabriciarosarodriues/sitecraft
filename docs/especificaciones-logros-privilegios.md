# Especificaciones funcionales - Logros y privilegios del alumno

## Objetivo

Describir el funcionamiento actual del sistema de progreso, racha, tiempo estudiado y privilegios visibles dentro del aula virtual del alumno.

---

## Alcance actual

El sistema se utiliza en:

- `public/student/curso.php`
- `app/views/student/curso.php`

Datos implicados:

- `study_logs`
- `progress_summary`
- `enrollment_lesson_progress` (creada dinámicamente si no existe)
- `course_media`, `course_lessons`, `course_modules`

En la versión actual, los logros se muestran como **privilegios desbloqueables calculados en tiempo real** para cada matrícula.

---

## Qué ve el alumno dentro del curso

En el aula del curso, el alumno puede ver:

- porcentaje global de progreso,
- minutos acumulados de estudio,
- racha de días consecutivos,
- contador de privilegios desbloqueados,
- listado de privilegios con su condición actual.

---

## Registro de actividad por lección

Cuando el alumno abre una lección:

1. se intenta registrar o actualizar `enrollment_lesson_progress`,
2. se marca la lección como vista,
3. se marca revisión de recursos si la lección tiene material visible,
4. se puede marcar manualmente como completada,
5. se suma tiempo estimado en `study_logs` una sola vez por lección y día.

### Tiempo estimado

El tiempo no se mide por reproducción real, sino por una estimación basada en la duración de la lección.

Regla actual:

- mínimo: `3 minutos`,
- máximo: `30 minutos`,
- valor por defecto si no hay duración: `8 minutos`.

Además, el sistema evita sumar repetidamente la misma lección el mismo día usando una clave de sesión diaria.

---

## Cálculo de la racha

La racha se obtiene a partir de los días distintos registrados en `study_logs` para la matrícula actual.

Reglas:

- se ordenan los días de estudio de más reciente a más antiguo,
- la racha comienza en el día más reciente registrado,
- si falta un día en la secuencia, la racha se rompe.

---

## Cálculo del progreso

El progreso actual se calcula por lección con una ponderación sencilla:

- `0.6` si la lección fue vista,
- `0.2` si los recursos se consideran revisados,
- `0.2` si la lección fue completada.

El resultado se transforma a porcentaje y se persiste en `progress_summary`.

---

## Privilegios definidos actualmente

### 1. Foro privado del curso

- Descripción: acceso al espacio privado de alumnos activos.
- Requisito: completar al menos el `20%` del curso.

### 2. Pack de recursos premium

- Descripción: material adicional y guías descargables.
- Requisito: `45%` completado y al menos `60 minutos` acumulados.

### 3. Sesión grupal con tutor

- Descripción: invitación a una sesión de seguimiento en directo.
- Requisito: `70%` completado y racha de `3 días`.

### 4. Certificado final habilitado

- Descripción: habilitación para solicitar el certificado final.
- Requisito: completar el `100%` del curso.

---

## Estado funcional actual

- Los privilegios se calculan dinámicamente al cargar el aula.
- El estado visual cambia entre bloqueado y desbloqueado.
- El alumno ve el requisito exacto de cada privilegio.
- El panel lateral muestra progreso, tiempo, racha y contador de logros.

---

## Limitaciones actuales

- Los privilegios no abren todavía páginas funcionales independientes.
- No se guardan como insignias permanentes en `user_badges`.
- El tiempo de estudio es estimado, no medido en tiempo real.
- El progreso se basa en señales de uso y no en pruebas o evaluaciones formales.

---

## Posibles ampliaciones futuras

- persistir logros como insignias reales,
- habilitar páginas o recursos exclusivos por privilegio,
- refinar el cálculo de progreso con tests o hitos pedagógicos,
- emitir notificaciones automáticas al desbloquear un privilegio.
