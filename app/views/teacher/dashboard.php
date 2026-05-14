<?php
// Vista del panel docente con estadisticas globales y cursos asignados.
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Bienvenido, <?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="text-muted">Panel de control para profesores</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= htmlspecialchars(appUrl('index.php'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Volver a inicio
            </a>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['totalStudents']; ?></h3>
                    <p>Estudiantes activos</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-book"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['activeCourses']; ?></h3>
                    <p>Cursos activos</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-briefcase"></i>
                </div>
                <div class="stat-content">
                    <h3>Activo</h3>
                    <p>Estado de cuenta</p>
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
                    <i class="bi bi-info-circle"></i> No tienes cursos asignados aún.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($myCourses as $course): ?>
                        <div class="col-md-6 mb-3">
                            <div class="course-card">
                                <div class="course-header">
                                    <h4><?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                    <span class="badge bg-success"><?= $course['student_count']; ?> estudiantes</span>
                                </div>
                                <p class="course-description"><?= htmlspecialchars(substr($course['description'] ?? 'Sin descripción', 0, 100), ENT_QUOTES, 'UTF-8'); ?>...</p>
                                <a href="<?= htmlspecialchars(appUrl('teacher/curso.php?slug=' . $course['slug']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-gear-fill me-1"></i>Administrar curso
                                </a>
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
