-- ============================================================
-- SEED: Notificaciones de Ejemplo
-- Ejecutar DESPUÉS de schema.sql
-- ============================================================

USE sitecraft;

-- Nota: Este seed crea notificaciones de ejemplo para testing.
-- En producción, las notificaciones se crean mediante código cuando:
-- - Se añade una lección nueva
-- - El alumno completa un hito
-- - El tutor envía un mensaje
-- - Etc.

-- Ejemplo: Notificaciones para el alumno de prueba Ana López (id=asumido 2)
-- (Primero asegúrate de que el usuario existe en la base de datos)

INSERT INTO notifications (user_id, type, channel, title, body, status, related_entity_type, related_entity_id, created_at) VALUES

-- Notificaciones de curso
(
    (SELECT id FROM users WHERE email = 'ana.lopez@sitecraft.local' LIMIT 1),
    'course_new_lesson',
    'in_app',
    'Nueva lección: Bases del ejercicio saludable',
    'Se ha añadido una nueva lección al Bloque 1. Aprenderás los principios científicos fundamentales del entrenamiento físico.',
    'read',
    'course',
    (SELECT id FROM courses WHERE slug = 'entrenador-personal-nivel-i' LIMIT 1),
    DATE_SUB(NOW(), INTERVAL 3 DAY)
),

(
    (SELECT id FROM users WHERE email = 'ana.lopez@sitecraft.local' LIMIT 1),
    'course_resource_ready',
    'in_app',
    'Recurso descargable disponible',
    'El PDF "Tabla de periodización anual" está listo para descargar en la lección de Bases del ejercicio saludable.',
    'read',
    'course',
    (SELECT id FROM courses WHERE slug = 'entrenador-personal-nivel-i' LIMIT 1),
    DATE_SUB(NOW(), INTERVAL 2 DAY)
),

-- Notificaciones de progreso
(
    (SELECT id FROM users WHERE email = 'ana.lopez@sitecraft.local' LIMIT 1),
    'progress_milestone',
    'in_app',
    '¡Hito alcanzado! 25% del curso completado',
    'Felicidades. Has completado el 25% del Curso de Entrenador Personal Nivel I. Mantén el ritmo.',
    'read',
    'enrollment',
    NULL,
    DATE_SUB(NOW(), INTERVAL 1 DAY)
),

(
    (SELECT id FROM users WHERE email = 'ana.lopez@sitecraft.local' LIMIT 1),
    'progress_unlock',
    'in_app',
    'Nuevo contenido desbloqueado',
    'Has desbloqueado acceso a los vídeos del Bloque 2: Evaluación de la Condición Física.',
    'read',
    'enrollment',
    NULL,
    DATE_SUB(NOW(), INTERVAL 12 HOUR)
),

-- Notificación de comunicación
(
    (SELECT id FROM users WHERE email = 'ana.lopez@sitecraft.local' LIMIT 1),
    'communication_tutor_message',
    'in_app',
    'Mensaje del tutor: Laura Martín',
    'Laura Martín ha dejado un comentario en tu envío del módulo 1. Haz clic para leerlo.',
    'pending',
    NULL,
    NULL,
    DATE_SUB(NOW(), INTERVAL 2 HOUR)
),

-- Notificación administrativa
(
    (SELECT id FROM users WHERE email = 'ana.lopez@sitecraft.local' LIMIT 1),
    'admin_enrollment_ending',
    'in_app',
    'Tu matrícula vence en 15 días',
    'Recuerda que tu acceso al Curso de Entrenador Personal Nivel I vence el próximo mes. Contacta con soporte si necesitas extender tu matrícula.',
    'pending',
    NULL,
    NULL,
    NOW()
);

-- Notificaciones para Carlos Ruiz (estado pending_payment)
INSERT INTO notifications (user_id, type, channel, title, body, status, related_entity_type, related_entity_id, created_at) VALUES

(
    (SELECT id FROM users WHERE email = 'carlos.ruiz@sitecraft.local' LIMIT 1),
    'admin_payment_pending',
    'in_app',
    'Pago pendiente para acceder al curso',
    'Tu inscripción al Curso de Entrenador Personal Nivel I está pendiente de pago. Completa el pago para acceder al contenido completo.',
    'pending',
    NULL,
    NULL,
    NOW()
);

-- Notificación del sistema
INSERT INTO notifications (user_id, type, channel, title, body, status, related_entity_type, related_entity_id, created_at) VALUES

(
    (SELECT id FROM users WHERE email = 'ana.lopez@sitecraft.local' LIMIT 1),
    'system_update',
    'in_app',
    'Actualización de plataforma completada',
    'Hemos mejorado el sistema de notificaciones y la interfaz del aula virtual. Disfruta de la nueva experiencia.',
    'read',
    NULL,
    NULL,
    DATE_SUB(NOW(), INTERVAL 5 DAY)
);

-- Nota sobre comentarios:
-- Las notificaciones deben generarse automáticamente mediante código en:
-- - app/services/NotificationService.php (lógica de negocio)
-- - app/controllers/NotificationController.php (controlador)
-- 
-- Ejemplos de cuándo generar notificaciones:
-- 1. Cuando se crea una nueva lección (en CourseController o service)
-- 2. Cuando un alumno completa un módulo (en StudyLogService)
-- 3. Cuando se alcanza un hito de progreso (en ProgressService)
-- 4. Cuando un tutor envía un mensaje (en ChatController)
-- 5. Cuando se ha completado un procesamiento de pago (en PaymentService)
-- 
-- Ejemplo en PHP:
-- $notificationController = new NotificationController($pdo);
-- $notificationController->createCourseNotification(
--     userId: $userId,
--     subType: 'new_lesson',
--     title: 'Nueva lección disponible',
--     body: 'Se ha añadido una nueva lección: ...',
--     courseId: $courseId
-- );
