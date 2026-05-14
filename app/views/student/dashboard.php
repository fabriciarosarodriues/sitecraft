<?php
// Vista principal del alumno con estadisticas y acceso a sus cursos.
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Hola, <?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="text-muted">Tu panel de estudiante - Continúa con tus cursos</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= htmlspecialchars(appUrl('index.php'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Ver más cursos
            </a>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-book-half"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['activeCourses'] ?? 0; ?></h3>
                    <p>Cursos activos</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['completedCourses'] ?? 0; ?></h3>
                    <p>Cursos completados</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Mis Cursos -->
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Mis Cursos</h2>
            <?php if (empty($myCourses)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Aún no te has inscrito en ningún curso. <a href="<?= htmlspecialchars(appUrl('index.php'), ENT_QUOTES, 'UTF-8'); ?>">Explora los cursos disponibles</a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($myCourses as $course): ?>
                        <div class="col-md-6 mb-3">
                            <div class="course-card">
                                <div class="course-header">
                                    <h4><?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                    <span class="badge bg-<?= match($course['enrollment_status']) {
                                        'active' => 'success',
                                        'completed' => 'primary',
                                        'paused' => 'warning',
                                        default => 'secondary'
                                    } ?>">
                                        <?= match($course['enrollment_status']) {
                                            'active' => 'En progreso',
                                            'completed' => 'Completado',
                                            'paused' => 'Pausado',
                                            'pending_payment' => 'Pago pendiente',
                                            default => 'Cancelado'
                                        } ?>
                                    </span>
                                </div>
                                <p class="course-description"><?= htmlspecialchars(substr($course['description'] ?? 'Sin descripción', 0, 100), ENT_QUOTES, 'UTF-8'); ?>...</p>
                                <?php if (!empty($course['teacher_name'])): ?>
                                    <p class="course-teacher">
                                        <i class="bi bi-person"></i> <?= htmlspecialchars($course['teacher_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                <?php endif; ?>
                                <a href="<?= htmlspecialchars(appUrl('student/curso.php?slug=' . $course['slug']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-primary">
                                    Acceder al curso
                                </a>
                                <?php if (($course['enrollment_status'] ?? '') === 'active' || ($course['enrollment_status'] ?? '') === 'completed'): ?>
                                    <a href="<?= htmlspecialchars(appUrl('student/chatbot.php?slug=' . $course['slug']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-dark ms-2">
                                        <i class="bi bi-chat-dots me-1"></i>Chatbot
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .stat-icon {
        font-size: 32px;
        color: #667eea;
    }

    .stat-content h3 {
        font-size: 24px;
        font-weight: 700;
        margin: 0;
        color: #333;
    }

    .stat-content p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }

    .course-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }

    .course-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .course-header h4 {
        margin: 0;
        color: #333;
        font-weight: 600;
    }

    .course-description {
        color: #666;
        font-size: 14px;
        margin: 10px 0;
    }

    .course-teacher {
        color: #667eea;
        font-size: 13px;
        margin: 5px 0;
    }

    .user-menu {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-name {
        color: #333;
        font-weight: 500;
    }
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
