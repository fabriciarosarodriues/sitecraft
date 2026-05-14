<?php

declare(strict_types=1);

require __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/index.php';

// Permite acceso solo a profesor/admin para gestionar curso.
requireAuth();
if (!hasRole('teacher') && !hasRole('admin')) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Acceso denegado.';
    exit;
}

$user = getCurrentUser();
$slug = trim($_GET['slug'] ?? '');

// Si no llega slug valido, volver al dashboard docente.
if ($slug === '') {
    header('Location: ' . appUrl('teacher/dashboard.php'));
    exit;
}

try {
    // Obtiene curso: admin ve cualquiera, profesor solo los suyos.
    if (hasRole('admin')) {
        $stmt = db()->prepare('SELECT * FROM courses WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
    } else {
        $stmt = db()->prepare(<<<'SQL'
            SELECT c.*
            FROM courses c
            JOIN course_teachers ct ON c.id = ct.course_id
            WHERE c.slug = ? AND ct.user_id = ?
            LIMIT 1
        SQL);
        $stmt->execute([$slug, $user['id']]);
    }
    $course = $stmt->fetch();

    if (!$course) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Curso no encontrado o sin permisos.';
        exit;
    }

    // Estadisticas agregadas de matriculas por estado.
    $stmt = db()->prepare(<<<'SQL'
        SELECT
            COUNT(CASE WHEN e.enrollment_status = 'active' THEN 1 END)    AS active_students,
            COUNT(CASE WHEN e.enrollment_status = 'completed' THEN 1 END) AS completed_students,
            COUNT(*)                                                        AS total_enrollments
        FROM enrollments e
        WHERE e.course_id = ?
    SQL);
    $stmt->execute([$course['id']]);
    $courseStats = $stmt->fetch();

    // Listado de alumnos con progreso acumulado por curso.
    $stmt = db()->prepare(<<<'SQL'
        SELECT
            u.id,
            u.full_name,
            u.email,
            e.enrollment_status,
            e.started_at,
            e.ends_at,
            COALESCE(ps.completion_percent, 0) AS completion_percent
        FROM enrollments e
        JOIN users u ON e.user_id = u.id
        LEFT JOIN progress_summary ps ON e.id = ps.enrollment_id
        WHERE e.course_id = ?
        ORDER BY e.created_at DESC
    SQL);
    $stmt->execute([$course['id']]);
    $students = $stmt->fetchAll();

} catch (Throwable $e) {
    // Valores por defecto para evitar caidas de renderizado.
    $course = null;
    $courseStats = ['active_students' => 0, 'completed_students' => 0, 'total_enrollments' => 0];
    $students = [];
}

$pageTitle = 'SITECRAFT | ' . htmlspecialchars($course['title'] ?? 'Curso', ENT_QUOTES, 'UTF-8');
$currentPage = 'teacher-dashboard';
$pageCss = [
    'assets/css/pages/teacher/dashboard.css',
    'assets/css/pages/teacher/curso.css',
];

require __DIR__ . '/../../app/views/teacher/curso.php';
