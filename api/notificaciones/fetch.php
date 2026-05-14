<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/index.php';
require_once __DIR__ . '/../../app/controllers/NotificationController.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$user = isAuthenticated() ? getCurrentUser() : null;

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$controller = new NotificationController(db());

try {
    match ($action) {
        'get' => handleGet($controller, $user),
        'mark-read' => handleMarkRead($controller, $user),
        'mark-all-read' => handleMarkAllRead($controller, $user),
        'archive' => handleArchive($controller, $user),
        'delete' => handleDelete($controller, $user),
        'stats' => handleStats($controller, $user),
        'count-unread' => handleCountUnread($controller, $user),
        default => response(['error' => 'Acción no reconocida'], 400),
    };
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleGet(NotificationController $controller, array $user): void
{
    $status = $_GET['status'] ?? 'all';
    $page = max(1, (int) ($_GET['page'] ?? 1));

    $data = $controller->getNotifications($user['id'], $status, $page);
    response($data);
}

function handleMarkRead(NotificationController $controller, array $user): void
{
    $notificationId = (int) ($_POST['id'] ?? 0);

    if ($notificationId === 0) {
        response(['error' => 'ID de notificación requerido'], 400);
        return;
    }

    $success = $controller->markAsRead($notificationId, $user['id']);

    if ($success) {
        response(['success' => true, 'message' => 'Notificación marcada como leída']);
    } else {
        response(['error' => 'No se pudo actualizar la notificación'], 403);
    }
}

function handleMarkAllRead(NotificationController $controller, array $user): void
{
    $success = $controller->markAllAsRead($user['id']);

    if ($success) {
        response(['success' => true, 'message' => 'Todas las notificaciones marcadas como leídas']);
    } else {
        response(['error' => 'Error al actualizar'], 500);
    }
}

function handleArchive(NotificationController $controller, array $user): void
{
    $notificationId = (int) ($_POST['id'] ?? 0);

    if ($notificationId === 0) {
        response(['error' => 'ID de notificación requerido'], 400);
        return;
    }

    $success = $controller->archiveNotification($notificationId, $user['id']);

    if ($success) {
        response(['success' => true, 'message' => 'Notificación archivada']);
    } else {
        response(['error' => 'No se pudo archivar la notificación'], 403);
    }
}

function handleDelete(NotificationController $controller, array $user): void
{
    $notificationId = (int) ($_POST['id'] ?? 0);

    if ($notificationId === 0) {
        response(['error' => 'ID de notificación requerido'], 400);
        return;
    }

    // Verificar que la notificación pertenece al usuario
    $pdo = db();
    $stmt = $pdo->prepare('SELECT user_id FROM notifications WHERE id = :id');
    $stmt->execute([':id' => $notificationId]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$notification || $notification['user_id'] !== $user['id']) {
        response(['error' => 'No tienes permiso para eliminar esta notificación'], 403);
        return;
    }

    $pdo->prepare('DELETE FROM notifications WHERE id = :id')->execute([':id' => $notificationId]);
    response(['success' => true, 'message' => 'Notificación eliminada']);
}

function handleStats(NotificationController $controller, array $user): void
{
    $stats = $controller->getStats($user['id']);
    response($stats);
}

function handleCountUnread(NotificationController $controller, array $user): void
{
    $pdo = db();
    $stmt = $pdo->prepare(<<<'SQL'
        SELECT COUNT(*) as count FROM notifications
        WHERE user_id = :user_id AND status = 'pending'
    SQL);
    $stmt->execute([':user_id' => $user['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    response(['unreadCount' => $result['count'] ?? 0]);
}

function response(mixed $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
