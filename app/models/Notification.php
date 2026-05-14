<?php

declare(strict_types=1);

class Notification
{
    public function __construct(private \PDO $pdo) {}

    /**
     * Crear una nueva notificación
     */
    public function create(
        int $userId,
        string $type,
        string $title,
        string $body,
        string $channel = 'in_app',
        ?string $relatedEntityType = null,
        ?int $relatedEntityId = null
    ): int {
        $stmt = $this->pdo->prepare(<<<'SQL'
            INSERT INTO notifications (user_id, type, channel, title, body, status, related_entity_type, related_entity_id)
            VALUES (:user_id, :type, :channel, :title, :body, 'pending', :related_entity_type, :related_entity_id)
        SQL);

        $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':channel' => $channel,
            ':title' => $title,
            ':body' => $body,
            ':related_entity_type' => $relatedEntityType,
            ':related_entity_id' => $relatedEntityId,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Obtener notificaciones de un usuario
     */
    public function getByUserId(int $userId, string $status = 'all', int $limit = 20, int $offset = 0): array
    {
        $query = 'SELECT * FROM notifications WHERE user_id = :user_id';
        $params = [':user_id' => $userId];

        if ($status !== 'all') {
            $query .= ' AND status = :status';
            $params[':status'] = $status;
        }

        $query .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtener notificación por ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM notifications WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    /**
     * Marcar notificación como leída
     */
    public function markAsRead(int $id): bool
    {
        $stmt = $this->pdo->prepare(<<<'SQL'
            UPDATE notifications
            SET status = 'read', read_at = NOW()
            WHERE id = :id
        SQL);

        return $stmt->execute([':id' => $id]);
    }

    /**
     * Marcar notificaciones como leídas por usuario
     */
    public function markAllAsRead(int $userId): bool
    {
        $stmt = $this->pdo->prepare(<<<'SQL'
            UPDATE notifications
            SET status = 'read', read_at = NOW()
            WHERE user_id = :user_id AND status != 'read'
        SQL);

        return $stmt->execute([':user_id' => $userId]);
    }

    /**
     * Archiva una notificación
     */
    public function archive(int $id): bool
    {
        $stmt = $this->pdo->prepare(<<<'SQL'
            UPDATE notifications
            SET status = 'archived'
            WHERE id = :id
        SQL);

        return $stmt->execute([':id' => $id]);
    }

    /**
     * Eliminar notificación
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM notifications WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }

    /**
     * Contar notificaciones no leídas
     */
    public function getUnreadCount(int $userId): int
    {
        $stmt = $this->pdo->prepare(<<<'SQL'
            SELECT COUNT(*) as count FROM notifications
            WHERE user_id = :user_id AND status = 'pending'
        SQL);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['count'] ?? 0;
    }

    /**
     * Obtener tipos de notificaciones disponibles
     */
    public function getTypes(): array
    {
        return [
            'course_new_lesson' => 'Nueva lección disponible',
            'course_content_updated' => 'Contenido actualizado',
            'course_resource_ready' => 'Recurso descargable listo',
            'course_event' => 'Evento especial del aula',
            'progress_incomplete_lesson' => 'Lecciones incompletas',
            'progress_milestone' => 'Hito de progreso alcanzado',
            'progress_unlock' => 'Contenido desbloqueado',
            'communication_tutor_message' => 'Mensaje del tutor',
            'communication_forum_reply' => 'Respuesta en foro',
            'admin_payment_pending' => 'Recordatorio de pago',
            'admin_enrollment_ending' => 'Fecha de fin de matrícula próxima',
            'admin_access_changed' => 'Cambios en acceso',
            'system_maintenance' => 'Mantenimiento programado',
            'system_update' => 'Actualización de plataforma',
            'system_policy_change' => 'Cambios en políticas',
        ];
    }
}
