# Sistema de Notificaciones - SITECRAFT

## Resumen

El sistema de notificaciones permite informar a los usuarios sobre eventos importantes del curso, cambios en su matrícula, mensajes del tutor y recordatorios de actividades pendientes.

Este documento describe el estado funcional actual del sistema implementado en el proyecto.

---

## Arquitectura

### Componentes principales

1. **Modelo Notification** (`app/models/Notification.php`)
   - Gestiona operaciones CRUD en la tabla `notifications`
   - Marca como leída, archiva, elimina notificaciones

2. **Controlador NotificationController** (`app/controllers/NotificationController.php`)
   - Lógica de negocio para notificaciones
   - Métodos para crear notificaciones especializadas (curso, progreso, admin)

3. **Servicio NotificationService** (`app/services/NotificationService.php`)
   - Crea automáticamente notificaciones para eventos del sistema
   - Métodos especializados para cada tipo de evento

4. **Vistas**
   - `app/views/student/notificaciones.php` - Centro de notificaciones
   - `public/assets/css/pages/student/notificaciones.css` - Estilos

5. **API**
   - `api/notificaciones/fetch.php` - Endpoints REST

---

## Tipos de Notificaciones

### Notificaciones de Curso
- `course_new_lesson` - Nueva lección disponible
- `course_content_updated` - Contenido actualizado
- `course_resource_ready` - Recurso descargable listo
- `course_event` - Evento especial del aula

### Notificaciones de Progreso
- `progress_incomplete_lesson` - Recordatorio de lecciones incompletas
- `progress_milestone` - Hito de avance alcanzado
- `progress_unlock` - Contenido desbloqueado

### Notificaciones de Comunicación
- `communication_tutor_message` - Mensaje nuevo del tutor
- `communication_forum_reply` - Respuesta en foro

### Notificaciones Administrativas
- `admin_payment_pending` - Recordatorio de pago pendiente
- `admin_enrollment_ending` - Fecha de fin de matrícula próxima
- `admin_access_changed` - Cambios en datos de acceso

### Notificaciones del Sistema
- `system_maintenance` - Mantenimiento programado
- `system_update` - Actualización de plataforma
- `system_policy_change` - Cambios en políticas

---

## Estados de Notificación

- **pending** - Notificación reciente, no leída
- **read** - El usuario ya vio la notificación
- **sent** - Enviada (email/SMS)
- **failed** - Error en envío

Nota técnica: la interfaz y el modelo contemplan la acción de archivar, pero si se reconstruye la base de datos desde `database/schema.sql` conviene revisar la enumeración del campo `notifications.status` para mantenerla alineada con el código.

---

## Canales de Notificación

- **in_app** - Notificación en la plataforma
- **email** - Envío por correo electrónico
- **whatsapp** - Envío por WhatsApp (requiere integración)

---

## Uso en Código

### 1. Crear notificación de curso

```php
require_once __DIR__ . '/app/services/NotificationService.php';

$notificationService = new NotificationService(db());

$notificationService->notifyNewLesson(
    userId: $userId,
    courseId: $courseId,
    lessonTitle: 'Bases del ejercicio saludable',
    lessonDescription: 'Aprenderás los principios científicos del entrenamiento'
);
```

### 2. Crear notificación de progreso

```php
$notificationService->notifyProgressMilestone(
    userId: $userId,
    enrollmentId: $enrollmentId,
    percentageCompleted: 50,
    milestoneName: '¡Mitad del camino!'
);
```

### 3. Crear notificación de pago

```php
$notificationService->notifyPaymentPending(
    userId: $userId,
    amount: 890.00,
    courseName: 'Entrenador Personal Nivel I'
);
```

### 4. Notificar a múltiples usuarios

```php
$notificationService->notifyAllEnrolledStudents(
    courseId: $courseId,
    type: 'course_new_lesson',
    title: 'Nueva lección: Evaluación Física',
    body: 'Se ha añadido una nueva lección al curso'
);
```

### 5. Crear notificación personalizada

```php
$controller = new NotificationController(db());

$notificationId = $controller->createCourseNotification(
    userId: $userId,
    subType: 'new_lesson',
    title: 'Nueva lección disponible',
    body: 'Se ha añadido una nueva lección',
    courseId: $courseId
);
```

---

## API Endpoints

### Obtener notificaciones

```
GET /api/notificaciones/fetch.php?action=get&status=all&page=1

Parámetros:
- status: 'all' | 'pending' | 'read' | 'archived'
- page: número de página

Respuesta:
{
  "notifications": [...],
  "unreadCount": 5,
  "page": 1,
  "perPage": 20
}
```

### Marcar como leída

```
POST /api/notificaciones/fetch.php?action=mark-read
Body: id=123

Respuesta:
{
  "success": true,
  "message": "Notificación marcada como leída"
}
```

### Marcar todas como leídas

```
POST /api/notificaciones/fetch.php?action=mark-all-read

Respuesta:
{
  "success": true,
  "message": "Todas las notificaciones marcadas como leídas"
}
```

### Archivar notificación

```
POST /api/notificaciones/fetch.php?action=archive
Body: id=123

Respuesta:
{
  "success": true,
  "message": "Notificación archivada"
}
```

### Eliminar notificación

```
POST /api/notificaciones/fetch.php?action=delete
Body: id=123

Respuesta:
{
  "success": true,
  "message": "Notificación eliminada"
}
```

### Obtener estadísticas

```
GET /api/notificaciones/fetch.php?action=stats

Respuesta:
{
  "unreadCount": 5,
  "byStatus": {
    "pending": 5,
    "read": 10,
    "archived": 3
  },
  "total": 18
}
```

### Contar no leídas

```
GET /api/notificaciones/fetch.php?action=count-unread

Respuesta:
{
  "unreadCount": 5
}
```

---

## Acceso a Notificaciones

### Página del Centro de Notificaciones

```
/SITECRAFT/public/student/notificaciones.php
```

**Acceso**: Solo alumnos autenticados

**Características**:
- Filtrar por estado
- Marcar como leída
- Archivar
- Eliminar
- Paginación

### Indicador de Notificaciones No Leídas

Para mostrar el contador de notificaciones no leídas en la navegación:

```php
<?php
$notificationCount = (new NotificationController(db()))->getStats($user['id'])['unreadCount'];
?>

<!-- En la navegación -->
<a href="/public/student/notificaciones.php" class="nav-link">
    <i class="bi bi-bell"></i>
    <?php if ($notificationCount > 0): ?>
        <span class="badge badge-danger"><?= $notificationCount ?></span>
    <?php endif; ?>
</a>
```

---

## Testing

### Insertar notificaciones de ejemplo

```bash
mysql -u root -p sitecraft < database/seeds/notificaciones-ejemplo.sql
```

Las notificaciones de ejemplo se crean con:
- Tipos variados
- Estados diferentes
- Usuarios de prueba (Ana López, Carlos Ruiz)

---

## Integración con Otros Componentes

### En el Controlador de Cursos

Cuando se crea una nueva lección:

```php
$notificationService->notifyAllEnrolledStudents(
    courseId: $courseId,
    type: 'course_new_lesson',
    title: "Nueva lección: {$lessonTitle}",
    body: $lessonDescription
);
```

### En el Controlador de Pagos

Cuando se completa un pago:

```php
$notificationService->notifyPaymentPending(
    userId: $userId,
    amount: $totalAmount,
    courseName: $courseName
);
```

### En el Servicio de Progreso

Cuando se alcanza un hito:

```php
if ($percentageCompleted >= 50 && $previousPercentage < 50) {
    $notificationService->notifyProgressMilestone(
        userId: $userId,
        enrollmentId: $enrollmentId,
        percentageCompleted: 50
    );
}
```

---

## Configuración

### En `app/config/database.php`

La tabla `notifications` ya está definida en el schema. No requiere configuración adicional.

### Personalización de Iconos

Los iconos se personalizan en `app/views/student/notificaciones.php` en la función `getNotificationIcon()`.

### Personalización de Estilos

Editar `public/assets/css/pages/student/notificaciones.css` para cambiar apariencia.

---

## Notas Importantes

1. **Seguridad**: Las notificaciones solo son visibles por el usuario propietario
2. **Performance**: Usa índices en `user_id` y `status` para consultas rápidas
3. **Escalabilidad**: Para muchas notificaciones, considera archivado automático después de 30 días
4. **Canales**: Email y WhatsApp requieren configuración de servicios externos en `NotificationService`

---

## Próximas Mejoras

- [ ] Notificaciones por email (integración SMTP)
- [ ] Notificaciones por WhatsApp (integración Twilio)
- [ ] Preferencias de notificación por usuario
- [ ] Plantillas personalizables
- [ ] Historial de notificaciones paginado
- [ ] Notificaciones en tiempo real (WebSocket)
- [ ] Panel de administración para enviar notificaciones masivas

---

## Archivos Relacionados

- Tabla DB: `notifications`, `message_queue`
- Modelo: `app/models/Notification.php`
- Controlador: `app/controllers/NotificationController.php`
- Servicio: `app/services/NotificationService.php`
- API: `api/notificaciones/fetch.php`
- Vista: `app/views/student/notificaciones.php`
- CSS: `public/assets/css/pages/student/notificaciones.css`
- Seeds: `database/seeds/notificaciones-ejemplo.sql`
- Página pública: `public/student/notificaciones.php`
