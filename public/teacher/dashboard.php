<?php

declare(strict_types=1);

require __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/index.php';

// Acceso restringido al panel docente para perfiles autorizados.
requireAuth();
if (!hasRole('teacher') && !hasRole('admin')) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Acceso denegado: no tienes permisos para acceder a esta página.';
    exit;
}

$user = getCurrentUser();
$pageTitle = 'SITECRAFT | Panel de Profesor';
$currentPage = 'teacher-dashboard';
$pageCss = ['assets/css/pages/teacher/dashboard.css'];

// Estructura base de estadisticas para renderizado del panel.
$stats = [
    'totalStudents' => 0,
    'activeCourses' => 0,
    'totalEarnings' => 0,
];

try {
    // Cuenta alumnos unicos asociados a cursos del profesor.
    $stmt = db()->prepare(<<<'SQL'
        SELECT COUNT(DISTINCT e.user_id) as count
        FROM enrollments e
        JOIN course_teachers ct ON e.course_id = ct.course_id
        WHERE ct.user_id = ?
    SQL);
    $stmt->execute([$user['id']]);
    $stats['totalStudents'] = (int) $stmt->fetch()['count'];

    // Cuenta cursos publicados asignados al docente.
    $stmt = db()->prepare(<<<'SQL'
        SELECT COUNT(*) as count
        FROM courses c
        JOIN course_teachers ct ON c.id = ct.course_id
        WHERE ct.user_id = ? AND c.is_published = 1
    SQL);
    $stmt->execute([$user['id']]);
    $stats['activeCourses'] = (int) $stmt->fetch()['count'];

    // Recupera listado de cursos con numero de alumnos activos.
    $stmt = db()->prepare(<<<'SQL'
        SELECT 
            c.id,
            c.slug,
            c.title,
            c.description,
            COUNT(e.id) as student_count
        FROM courses c
        JOIN course_teachers ct ON c.id = ct.course_id
        LEFT JOIN enrollments e ON c.id = e.course_id AND e.enrollment_status = 'active'
        WHERE ct.user_id = ?
        GROUP BY c.id, c.slug, c.title, c.description
        ORDER BY c.created_at DESC
    SQL);
    $stmt->execute([$user['id']]);
    $myCourses = $stmt->fetchAll();
} catch (Throwable $e) {
    // Fallback seguro para no romper la vista del panel.
    $myCourses = [];
}

require __DIR__ . '/../../app/views/teacher/dashboard.php';
