<?php
// Centro de notificaciones del alumno con filtros, acciones y paginación.

// Devuelve el icono visual más adecuado según el tipo de notificación.
function getNotificationIcon(string $type): string
{
    return match ($type) {
        'course_new_lesson' => '<i class="bi bi-book-half" style="color: #0d6efd;"></i>',
        'course_content_updated' => '<i class="bi bi-arrow-repeat" style="color: #0d6efd;"></i>',
        'course_resource_ready' => '<i class="bi bi-download" style="color: #0d6efd;"></i>',
        'course_event' => '<i class="bi bi-calendar-event" style="color: #0d6efd;"></i>',
        'progress_incomplete_lesson' => '<i class="bi bi-exclamation-triangle" style="color: #f39c12;"></i>',
        'progress_milestone' => '<i class="bi bi-trophy" style="color: #28a745;"></i>',
        'progress_unlock' => '<i class="bi bi-unlock" style="color: #28a745;"></i>',
        'communication_tutor_message' => '<i class="bi bi-chat-left-text" style="color: #6c63ff;"></i>',
        'communication_forum_reply' => '<i class="bi bi-chat-dots" style="color: #6c63ff;"></i>',
        'admin_payment_pending' => '<i class="bi bi-credit-card" style="color: #dc3545;"></i>',
        'admin_enrollment_ending' => '<i class="bi bi-clock-history" style="color: #dc3545;"></i>',
        'admin_access_changed' => '<i class="bi bi-shield-exclamation" style="color: #dc3545;"></i>',
        'system_maintenance' => '<i class="bi bi-tools" style="color: #6c757d;"></i>',
        'system_update' => '<i class="bi bi-cloud-download" style="color: #6c757d;"></i>',
        'system_policy_change' => '<i class="bi bi-file-text" style="color: #6c757d;"></i>',
        default => '<i class="bi bi-bell"></i>',
    };
}

// Traduce códigos internos de tipo a etiquetas legibles para la interfaz.
function getNotificationTypeLabel(string $type): string
{
    $types = [
        'course_new_lesson' => 'Nueva lección',
        'course_content_updated' => 'Contenido actualizado',
        'course_resource_ready' => 'Recurso descargable',
        'course_event' => 'Evento del curso',
        'progress_incomplete_lesson' => 'Lecciones incompletas',
        'progress_milestone' => 'Hito alcanzado',
        'progress_unlock' => 'Contenido desbloqueado',
        'communication_tutor_message' => 'Mensaje del tutor',
        'communication_forum_reply' => 'Respuesta en foro',
        'admin_payment_pending' => 'Pago pendiente',
        'admin_enrollment_ending' => 'Fin de matrícula próximo',
        'admin_access_changed' => 'Cambio en acceso',
        'system_maintenance' => 'Mantenimiento',
        'system_update' => 'Actualización',
        'system_policy_change' => 'Cambios en políticas',
    ];

    return $types[$type] ?? 'Notificación';
}

// Devuelve la etiqueta de estado que ve el usuario final.
function getStatusLabel(string $status): string
{
    return match ($status) {
        'pending' => 'No leída',
        'read' => 'Leída',
        'archived' => 'Archivada',
        'sent' => 'Enviada',
        'failed' => 'Error',
        default => ucfirst($status),
    };
}

// Asigna la clase visual de la insignia según el estado actual.
function getStatusBadgeClass(string $status): string
{
    return match ($status) {
        'pending' => 'badge-warning',
        'read' => 'badge-success',
        'archived' => 'badge-secondary',
        'sent' => 'badge-info',
        'failed' => 'badge-danger',
        default => 'badge-secondary',
    };
}

// Convierte una fecha absoluta en un texto relativo sencillo para la UI.
function getRelativeTime(string $timestamp): string
{
    $time = strtotime($timestamp);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'Hace unos segundos';
    }

    if ($diff < 3600) {
        $minutes = intdiv($diff, 60);
        return "Hace {$minutes} " . ($minutes === 1 ? 'minuto' : 'minutos');
    }

    if ($diff < 86400) {
        $hours = intdiv($diff, 3600);
        return "Hace {$hours} " . ($hours === 1 ? 'hora' : 'horas');
    }

    if ($diff < 604800) {
        $days = intdiv($diff, 86400);
        return "Hace {$days} " . ($days === 1 ? 'día' : 'días');
    }

    return date('d/m/Y H:i', $time);
}
?>

<div class="notifications-container">
    <div class="notifications-header">
        <h2 class="notifications-title">
            <i class="bi bi-bell"></i> Centro de Notificaciones
        </h2>
        <?php if ($unreadCount > 0): ?>
            <div class="notifications-badge">
                <span class="badge badge-danger"><?= htmlspecialchars($unreadCount, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>
    </div>

    <div class="notifications-filters mb-4">
        <div class="btn-group" role="group">
            <a href="#" class="filter-btn active" data-filter="all">
                <i class="bi bi-funnel"></i> Todas
            </a>
            <a href="#" class="filter-btn" data-filter="pending">
                <i class="bi bi-exclamation-circle"></i> No leídas
            </a>
            <a href="#" class="filter-btn" data-filter="read">
                <i class="bi bi-check-circle"></i> Leídas
            </a>
            <a href="#" class="filter-btn" data-filter="archived">
                <i class="bi bi-archive"></i> Archivadas
            </a>
        </div>
    </div>

    <div class="notifications-list">
        <?php if (empty($notifications)): ?>
            <div class="notifications-empty">
                <i class="bi bi-inbox"></i>
                <p>No tienes notificaciones en este momento.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-card" data-notification-id="<?= (int) $notification['id'] ?>" data-status="<?= htmlspecialchars($notification['status'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="notification-card__icon">
                        <?= getNotificationIcon($notification['type']) ?>
                    </div>

                    <div class="notification-card__content">
                        <div class="notification-card__header">
                            <h4 class="notification-card__title">
                                <?= htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8') ?>
                            </h4>
                            <span class="notification-card__time">
                                <?= getRelativeTime($notification['created_at']) ?>
                            </span>
                        </div>

                        <p class="notification-card__body">
                            <?= htmlspecialchars($notification['body'], ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <div class="notification-card__meta">
                            <span class="notification-card__type">
                                <?= getNotificationTypeLabel($notification['type']) ?>
                            </span>
                            <span class="notification-card__status badge <?= getStatusBadgeClass($notification['status']) ?>">
                                <?= getStatusLabel($notification['status']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="notification-card__actions">
                        <?php if ($notification['status'] !== 'read'): ?>
                            <button class="btn btn-sm btn-outline-primary mark-read" title="Marcar como leída">
                                <i class="bi bi-check"></i>
                            </button>
                        <?php endif; ?>

                        <?php if ($notification['status'] !== 'archived'): ?>
                            <button class="btn btn-sm btn-outline-secondary archive-notification" title="Archivar">
                                <i class="bi bi-archive"></i>
                            </button>
                        <?php endif; ?>

                        <button class="btn btn-sm btn-outline-danger delete-notification" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($notifications) && count($notifications) >= 20): ?>
        <div class="notifications-pagination mt-4 d-flex justify-content-center">
            <nav>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>">
                                <i class="bi bi-chevron-left"></i> Anterior
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="page-item active">
                        <span class="page-link">Página <?= $page ?></span>
                    </li>

                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">
                            Siguiente <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Estilos específicos del centro de notificaciones. -->
<style>
    .notifications-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .notifications-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e9ecef;
    }

    .notifications-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .notifications-badge {
        position: relative;
    }

    .notifications-badge .badge {
        font-size: 0.875rem;
        padding: 0.35rem 0.65rem;
    }

    .notifications-filters {
        display: flex;
        gap: 0.5rem;
    }

    .btn-group {
        display: flex;
        gap: 0.5rem;
    }

    .filter-btn {
        padding: 0.5rem 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: #ffffff;
        color: #495057;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background-color: #0d6efd;
        color: #ffffff;
        border-color: #0d6efd;
    }

    .notifications-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .notifications-empty {
        text-align: center;
        padding: 3rem 1rem;
        color: #6c757d;
    }

    .notifications-empty i {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
        opacity: 0.5;
    }

    .notification-card {
        display: flex;
        gap: 1rem;
        padding: 1.25rem;
        background-color: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        align-items: flex-start;
    }

    .notification-card:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-color: #dee2e6;
    }

    .notification-card[data-status="pending"] {
        background-color: #f0f7ff;
        border-color: #b6d4ff;
    }

    .notification-card[data-status="read"] {
        opacity: 0.75;
    }

    .notification-card__icon {
        font-size: 1.5rem;
        flex-shrink: 0;
        padding-top: 0.25rem;
    }

    .notification-card__content {
        flex: 1;
        min-width: 0;
    }

    .notification-card__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.5rem;
        gap: 1rem;
    }

    .notification-card__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 600;
        color: #1a1a1a;
    }

    .notification-card__time {
        font-size: 0.85rem;
        color: #6c757d;
        flex-shrink: 0;
        white-space: nowrap;
    }

    .notification-card__body {
        margin: 0.5rem 0;
        font-size: 0.95rem;
        color: #495057;
        line-height: 1.5;
    }

    .notification-card__meta {
        display: flex;
        gap: 1rem;
        align-items: center;
        font-size: 0.85rem;
        margin-top: 0.75rem;
    }

    .notification-card__type {
        color: #6c757d;
        background-color: #f8f9fa;
        padding: 0.25rem 0.75rem;
        border-radius: 0.25rem;
    }

    .notification-card__status {
        font-size: 0.75rem;
    }

    .notification-card__actions {
        display: flex;
        gap: 0.5rem;
        flex-shrink: 0;
        align-items: center;
    }

    .notifications-pagination {
        margin-top: 2rem;
    }

    @media (max-width: 576px) {
        .notification-card {
            flex-direction: column;
        }

        .notification-card__header {
            flex-direction: column;
        }

        .notification-card__actions {
            width: 100%;
            justify-content: flex-end;
        }

        .btn-group {
            flex-wrap: wrap;
        }
    }
</style>
