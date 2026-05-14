<?php

declare(strict_types=1);

require __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/index.php';
require_once __DIR__ . '/../../app/controllers/NotificationController.php';

// Resuelve la sesión actual; si no existe, se redirige al inicio.
$user = isAuthenticated() ? getCurrentUser() : null;

if (!$user) {
    header('Location: index.php');
    exit;
}

// Verifica rol estudiante para restringir este centro de notificaciones.
$pdo = db();
$stmt = $pdo->prepare(<<<'SQL'
    SELECT 1 FROM user_roles ur
    JOIN roles r ON r.id = ur.role_id
    WHERE ur.user_id = :user_id AND r.code = 'student'
    LIMIT 1
SQL);
$stmt->execute([':user_id' => $user['id']]);

if (!$stmt->fetch()) {
    header('Location: index.php');
    exit;
}

$controller = new NotificationController($pdo);
$status = $_GET['status'] ?? 'all';
$page = max(1, (int) ($_GET['page'] ?? 1));

// Carga el lote paginado y las estadísticas de notificaciones.
$data = $controller->getNotifications($user['id'], $status, $page);
$notifications = $data['notifications'];
$unreadCount = $data['unreadCount'];
$stats = $controller->getStats($user['id']);

$pageTitle = 'SITECRAFT | Centro de Notificaciones';
$currentPage = 'student';
$pageCss = ['assets/css/pages/student/notificaciones.css'];

require __DIR__ . '/../../app/views/layouts/header.php';
?>

<main class="main-content">
    <div class="container-fluid py-4">
        <div class="student-notifications">
            <?php require __DIR__ . '/../../app/views/student/notificaciones.php'; ?>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestiona filtros por estado sin recargar logica adicional en servidor.
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const filter = this.dataset.filter;
                const url = new URL(window.location);
                url.searchParams.set('status', filter);
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
            });
        });

        // Marca notificacion como leida mediante endpoint AJAX.
        document.querySelectorAll('.mark-read').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const notificationId = this.closest('.notification-card').dataset.notificationId;
                markAsRead(notificationId);
            });
        });

        // Mueve notificacion al estado archivado.
        document.querySelectorAll('.archive-notification').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const notificationId = this.closest('.notification-card').dataset.notificationId;
                archiveNotification(notificationId);
            });
        });

        // Elimina notificacion tras confirmacion explicita del usuario.
        document.querySelectorAll('.delete-notification').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('¿Deseas eliminar esta notificación?')) {
                    const notificationId = this.closest('.notification-card').dataset.notificationId;
                    deleteNotification(notificationId);
                }
            });
        });
    });

    function markAsRead(id) {
        // Llama al endpoint de marcado y recarga para refrescar contadores.
        fetch('api/notificaciones/fetch.php?action=mark-read', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
        })
        .catch(e => console.error(e));
    }

    function archiveNotification(id) {
        // Solicita archivado de una notificacion especifica.
        fetch('api/notificaciones/fetch.php?action=archive', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
        })
        .catch(e => console.error(e));
    }

    function deleteNotification(id) {
        // Solicita el borrado permanente de la notificación seleccionada.
        fetch('api/notificaciones/fetch.php?action=delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
        })
        .catch(e => console.error(e));
    }
</script>

<?php require __DIR__ . '/../../app/views/layouts/footer.php'; ?>
