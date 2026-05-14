<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Notification.php';

/**
 * NotificationService
 * Servicio para crear y gestionar notificaciones automáticas del sistema
 */
class NotificationService
{
    private Notification $notificationModel;

    public function __construct(private \PDO $pdo)
    {
        $this->notificationModel = new Notification($pdo);
    }

    /**
     * Notificar cuando hay una nueva lección
     */
    public function notifyNewLesson(int $userId, int $courseId, string $lessonTitle, string $lessonDescription = ''): void
    {
        $body = !empty($lessonDescription) 
            ? $lessonDescription
            : "Se ha añadido una nueva lección al curso.";

        $this->notificationModel->create(
            userId: $userId,
            type: 'course_new_lesson',
            title: "Nueva lección: {$lessonTitle}",
            body: $body,
            channel: 'in_app',
            relatedEntityType: 'course',
            relatedEntityId: $courseId,
        );
    }

    /**
     * Notificar cuando hay contenido actualizado
     */
    public function notifyContentUpdated(int $userId, int $courseId, string $moduleName, string $changes = ''): void
    {
        $body = !empty($changes) 
            ? "Cambios en {$moduleName}: {$changes}"
            : "El contenido del módulo {$moduleName} ha sido actualizado.";

        $this->notificationModel->create(
            userId: $userId,
            type: 'course_content_updated',
            title: 'Contenido actualizado',
            body: $body,
            channel: 'in_app',
            relatedEntityType: 'course',
            relatedEntityId: $courseId,
        );
    }

    /**
     * Notificar cuando un recurso está listo para descargar
     */
    public function notifyResourceReady(int $userId, int $courseId, string $resourceName, string $lessonName = ''): void
    {
        $title = "Recurso disponible: {$resourceName}";
        $body = !empty($lessonName) 
            ? "El archivo {$resourceName} está listo en la lección {$lessonName}."
            : "Un nuevo recurso {$resourceName} está disponible para descargar.";

        $this->notificationModel->create(
            userId: $userId,
            type: 'course_resource_ready',
            title: $title,
            body: $body,
            channel: 'in_app',
            relatedEntityType: 'course',
            relatedEntityId: $courseId,
        );
    }

    /**
     * Notificar cuando hay un evento especial en el aula
     */
    public function notifyCourseEvent(int $userId, int $courseId, string $eventName, string $description = ''): void
    {
        $body = !empty($description) 
            ? $description
            : "Hay un evento especial en el aula: {$eventName}";

        $this->notificationModel->create(
            userId: $userId,
            type: 'course_event',
            title: $eventName,
            body: $body,
            channel: 'in_app',
            relatedEntityType: 'course',
            relatedEntityId: $courseId,
        );
    }

    /**
     * Notificar lecciones incompletas
     */
    public function notifyIncompleteLessons(int $userId, int $enrollmentId, int $incompleteCount): void
    {
        $this->notificationModel->create(
            userId: $userId,
            type: 'progress_incomplete_lesson',
            title: 'Lecciones incompletas pendientes',
            body: "Tienes {$incompleteCount} lecciones sin completar. Intenta avanzar en tu curso.",
            channel: 'in_app',
            relatedEntityType: 'enrollment',
            relatedEntityId: $enrollmentId,
        );
    }

    /**
     * Notificar hito de progreso alcanzado
     */
    public function notifyProgressMilestone(int $userId, int $enrollmentId, int $percentageCompleted, string $milestoneName = ''): void
    {
        $title = !empty($milestoneName) 
            ? "¡{$milestoneName}! {$percentageCompleted}% completado"
            : "¡Hito alcanzado! {$percentageCompleted}% del curso completado";

        $body = "Felicidades. Has completado el {$percentageCompleted}% del curso. Mantén el ritmo.";

        $this->notificationModel->create(
            userId: $userId,
            type: 'progress_milestone',
            title: $title,
            body: $body,
            channel: 'in_app',
            relatedEntityType: 'enrollment',
            relatedEntityId: $enrollmentId,
        );
    }

    /**
     * Notificar desbloqueo de contenido
     */
    public function notifyUnlock(int $userId, int $enrollmentId, string $unlockedContent): void
    {
        $this->notificationModel->create(
            userId: $userId,
            type: 'progress_unlock',
            title: 'Nuevo contenido desbloqueado',
            body: "Has desbloqueado acceso a: {$unlockedContent}",
            channel: 'in_app',
            relatedEntityType: 'enrollment',
            relatedEntityId: $enrollmentId,
        );
    }

    /**
     * Notificar mensaje del tutor
     */
    public function notifyTutorMessage(int $userId, string $tutorName, string $messagePreview = '', ?int $courseId = null): void
    {
        $body = !empty($messagePreview) 
            ? "{$tutorName} ha dejado un comentario: {$messagePreview}"
            : "{$tutorName} te ha enviado un mensaje.";

        $this->notificationModel->create(
            userId: $userId,
            type: 'communication_tutor_message',
            title: "Mensaje del tutor: {$tutorName}",
            body: $body,
            channel: 'in_app',
            relatedEntityType: $courseId ? 'course' : null,
            relatedEntityId: $courseId,
        );
    }

    /**
     * Notificar respuesta en foro
     */
    public function notifyForumReply(int $userId, string $forumTopicTitle, string $replyAuthor = ''): void
    {
        $body = !empty($replyAuthor) 
            ? "{$replyAuthor} ha respondido a tu pregunta en: {$forumTopicTitle}"
            : "Hay una nueva respuesta en: {$forumTopicTitle}";

        $this->notificationModel->create(
            userId: $userId,
            type: 'communication_forum_reply',
            title: 'Nueva respuesta en foro',
            body: $body,
            channel: 'in_app',
        );
    }

    /**
     * Notificar pago pendiente
     */
    public function notifyPaymentPending(int $userId, float $amount, string $courseName = ''): void
    {
        $title = "Pago pendiente: €{$amount}";
        $body = !empty($courseName) 
            ? "Tu inscripción a {$courseName} está pendiente de pago de €{$amount}. Completa el pago para acceder."
            : "Tienes un pago pendiente de €{$amount}. Completa el pago para acceder a tu contenido.";

        $this->notificationModel->create(
            userId: $userId,
            type: 'admin_payment_pending',
            title: $title,
            body: $body,
            channel: 'in_app',
        );
    }

    /**
     * Notificar que la matrícula está próxima a vencer
     */
    public function notifyEnrollmentEnding(int $userId, string $courseName, int $daysLeft): void
    {
        $plural = $daysLeft === 1 ? 'día' : 'días';
        $title = "Tu matrícula vence en {$daysLeft} {$plural}";
        $body = "Tu acceso a {$courseName} vence en {$daysLeft} {$plural}. Contacta con soporte si necesitas extender tu matrícula.";

        $this->notificationModel->create(
            userId: $userId,
            type: 'admin_enrollment_ending',
            title: $title,
            body: $body,
            channel: 'in_app',
        );
    }

    /**
     * Notificar cambios en acceso
     */
    public function notifyAccessChanged(int $userId, string $changeDetails = ''): void
    {
        $body = !empty($changeDetails) 
            ? "Se han detectado cambios en tu acceso: {$changeDetails}"
            : 'Se han detectado cambios en tu acceso a la plataforma.';

        $this->notificationModel->create(
            userId: $userId,
            type: 'admin_access_changed',
            title: 'Cambios en tu acceso',
            body: $body,
            channel: 'in_app',
        );
    }

    /**
     * Notificar mantenimiento programado
     */
    public function notifyMaintenance(int $userId, string $maintenanceTime = ''): void
    {
        $body = !empty($maintenanceTime) 
            ? "Realizaremos mantenimiento programado el {$maintenanceTime}. El servicio puede no estar disponible durante este tiempo."
            : 'Se realizará mantenimiento programado próximamente.';

        $this->notificationModel->create(
            userId: $userId,
            type: 'system_maintenance',
            title: 'Mantenimiento programado',
            body: $body,
            channel: 'in_app',
        );
    }

    /**
     * Notificar actualización del sistema
     */
    public function notifySystemUpdate(int $userId, string $updateDetails = ''): void
    {
        $body = !empty($updateDetails) 
            ? "Hemos mejorado la plataforma: {$updateDetails}"
            : 'La plataforma ha sido actualizada con mejoras.';

        $this->notificationModel->create(
            userId: $userId,
            type: 'system_update',
            title: 'Actualización de plataforma',
            body: $body,
            channel: 'in_app',
        );
    }

    /**
     * Notificar cambios en políticas
     */
    public function notifyPolicyChange(int $userId, string $policyName = '', string $details = ''): void
    {
        $title = !empty($policyName) 
            ? "Cambios en {$policyName}"
            : 'Cambios en nuestras políticas';

        $body = !empty($details) 
            ? $details
            : 'Hemos actualizado nuestras políticas de privacidad y términos de servicio.';

        $this->notificationModel->create(
            userId: $userId,
            type: 'system_policy_change',
            title: $title,
            body: $body,
            channel: 'in_app',
        );
    }

    /**
     * Notificar a múltiples usuarios del mismo evento
     */
    public function notifyMultiple(array $userIds, string $type, string $title, string $body, string $channel = 'in_app', ?string $entityType = null, ?int $entityId = null): void
    {
        foreach ($userIds as $userId) {
            $this->notificationModel->create(
                userId: $userId,
                type: $type,
                title: $title,
                body: $body,
                channel: $channel,
                relatedEntityType: $entityType,
                relatedEntityId: $entityId,
            );
        }
    }

    /**
     * Notificar a todos los alumnos de un curso
     */
    public function notifyAllEnrolledStudents(int $courseId, string $type, string $title, string $body): void
    {
        $stmt = $this->pdo->prepare(<<<'SQL'
            SELECT DISTINCT u.id
            FROM users u
            JOIN enrollments e ON e.user_id = u.id
            WHERE e.course_id = :course_id
            AND e.enrollment_status IN ('active', 'completed', 'paused')
        SQL);
        $stmt->execute([':course_id' => $courseId]);
        $users = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $this->notifyMultiple(
            userIds: $users,
            type: $type,
            title: $title,
            body: $body,
            channel: 'in_app',
            entityType: 'course',
            entityId: $courseId,
        );
    }
}
