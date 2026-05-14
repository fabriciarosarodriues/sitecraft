<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Notification.php';

class NotificationController
{
    private Notification $notificationModel;

    public function __construct(private \PDO $pdo)
    {
        $this->notificationModel = new Notification($pdo);
    }

    /**
     * Obtener notificaciones del usuario autenticado
     */
    public function getNotifications(int $userId, string $status = 'all', int $page = 1): array
    {
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $notifications = $this->notificationModel->getByUserId($userId, $status, $limit, $offset);
        $unreadCount = $this->notificationModel->getUnreadCount($userId);

        return [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'page' => $page,
            'perPage' => $limit,
        ];
    }

    /**
     * Marcar notificación como leída
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = $this->notificationModel->getById($notificationId);

        if (!$notification || $notification['user_id'] !== $userId) {
            return false;
        }

        return $this->notificationModel->markAsRead($notificationId);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead(int $userId): bool
    {
        return $this->notificationModel->markAllAsRead($userId);
    }

    /**
     * Archivar notificación
     */
    public function archiveNotification(int $notificationId, int $userId): bool
    {
        $notification = $this->notificationModel->getById($notificationId);

        if (!$notification || $notification['user_id'] !== $userId) {
            return false;
        }

        return $this->notificationModel->archive($notificationId);
    }

    /**
     * Obtener estadísticas de notificaciones
     */
    public function getStats(int $userId): array
    {
        $unread = $this->notificationModel->getUnreadCount($userId);
        $all = $this->notificationModel->getByUserId($userId, 'all', 1000);

        $byStatus = [
            'pending' => 0,
            'read' => 0,
            'archived' => 0,
        ];

        foreach ($all as $notification) {
            $status = $notification['status'];
            if (isset($byStatus[$status])) {
                $byStatus[$status]++;
            }
        }

        return [
            'unreadCount' => $unread,
            'byStatus' => $byStatus,
            'total' => count($all),
        ];
    }

    /**
     * Crear notificación de curso
     */
    public function createCourseNotification(
        int $userId,
        string $subType,
        string $title,
        string $body,
        ?int $courseId = null
    ): int {
        return $this->notificationModel->create(
            userId: $userId,
            type: 'course_' . $subType,
            title: $title,
            body: $body,
            channel: 'in_app',
            relatedEntityType: 'course',
            relatedEntityId: $courseId,
        );
    }

    /**
     * Crear notificación de progreso
     */
    public function createProgressNotification(
        int $userId,
        string $subType,
        string $title,
        string $body,
        ?int $enrollmentId = null
    ): int {
        return $this->notificationModel->create(
            userId: $userId,
            type: 'progress_' . $subType,
            title: $title,
            body: $body,
            channel: 'in_app',
            relatedEntityType: 'enrollment',
            relatedEntityId: $enrollmentId,
        );
    }

    /**
     * Crear notificación administrativa
     */
    public function createAdminNotification(
        int $userId,
        string $subType,
        string $title,
        string $body,
        string $channel = 'in_app'
    ): int {
        return $this->notificationModel->create(
            userId: $userId,
            type: 'admin_' . $subType,
            title: $title,
            body: $body,
            channel: $channel,
        );
    }
}
