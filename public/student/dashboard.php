<?php

declare(strict_types=1);

require __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/index.php';

// Protege el panel: solo usuarios autenticados pueden entrar.
requireAuth();

$user = getCurrentUser();
$pageTitle = 'SITECRAFT | Mi Panel';
$currentPage = 'student-dashboard';
$pageCss = ['assets/css/pages/student/dashboard.css'];

// Contenedores del dashboard para cursos y metricas.
$myCourses = [];
$teacherCourses = [];
$stats = [
    'activeCourses' => 0,
    'completedCourses' => 0,
    'teachingCourses' => 0,
    'teachingStudents' => 0,
];

try {
    // Consulta de cursos matriculados del alumno actual.
    $stmt = db()->prepare(<<<'SQL'
        SELECT 
            c.id,
            c.slug,
            c.title,
            c.description,
            e.enrollment_status,
            e.started_at,
            e.ends_at,
            u.full_name as teacher_name
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        LEFT JOIN course_teachers ct ON c.id = ct.course_id
        LEFT JOIN users u ON ct.user_id = u.id
        WHERE e.user_id = ?
        ORDER BY e.started_at DESC
    SQL);
    $stmt->execute([$user['id']]);
    $myCourses = $stmt->fetchAll();

    // Estados considerados como cursos activos para estadisticas visuales.
    $activeEnrollmentStatuses = ['active', 'in_progress', 'paused'];

    $stats['activeCourses'] = count(array_filter(
        $myCourses,
        static function (array $course) use ($activeEnrollmentStatuses): bool {
            $status = strtolower(trim((string) ($course['enrollment_status'] ?? '')));
            return in_array($status, $activeEnrollmentStatuses, true);
        }
    ));

    $stats['completedCourses'] = count(array_filter(
        $myCourses,
        static function (array $course): bool {
            $status = strtolower(trim((string) ($course['enrollment_status'] ?? '')));
            return $status === 'completed';
        }
    ));

    if (hasRole('teacher')) {
        // Si tambien es profesor, carga resumen de sus cursos impartidos.
        $stmt = db()->prepare(<<<'SQL'
            SELECT
                c.id,
                c.slug,
                c.title,
                c.description,
                COUNT(DISTINCT e.id) AS total_students,
                COALESCE(AVG(ps.completion_percent), 0) AS avg_progress,
                COUNT(DISTINCT CASE WHEN cm.message_role = 'user' THEN cm.id END) AS student_questions
            FROM courses c
            JOIN course_teachers ct ON c.id = ct.course_id
            LEFT JOIN enrollments e ON e.course_id = c.id
            LEFT JOIN progress_summary ps ON ps.enrollment_id = e.id
            LEFT JOIN chat_messages cm ON cm.course_id = c.id
            WHERE ct.user_id = ?
            GROUP BY c.id, c.slug, c.title, c.description
            ORDER BY c.created_at DESC
        SQL);
        $stmt->execute([$user['id']]);
        $teacherCourses = $stmt->fetchAll();

        $stats['teachingCourses'] = count($teacherCourses);
        $stats['teachingStudents'] = array_sum(array_map(
            static fn(array $course): int => (int) ($course['total_students'] ?? 0),
            $teacherCourses
        ));
    }
} catch (Throwable $e) {
    // Si algo falla, mantener dashboard estable con valores vacios.
    $myCourses = [];
    $teacherCourses = [];
        $stats = [
            'activeCourses' => 0,
            'completedCourses' => 0,
            'teachingCourses' => 0,
            'teachingStudents' => 0,
        ];
    }

require __DIR__ . '/../../app/views/student/dashboard.php';
