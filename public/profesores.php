<?php

declare(strict_types=1);

require __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/index.php';

// Configuracion de pagina publica de profesores.
$pageTitle = 'SITECRAFT | Profesores';
$currentPage = 'teachers';
$pageCss = ['assets/css/pages/home/landing.css'];

$teachers = [];
$loadError = null;

try {
    // Recupera profesores y cursos asociados en una sola consulta.
    $statement = db()->query(<<<'SQL'
        SELECT
            u.id,
            u.full_name,
            u.email,
            c.title AS course_title,
            c.slug AS course_slug,
            c.is_published
        FROM users u
        INNER JOIN user_roles ur ON ur.user_id = u.id
        INNER JOIN roles r ON r.id = ur.role_id
        LEFT JOIN course_teachers ct ON ct.user_id = u.id
        LEFT JOIN courses c ON c.id = ct.course_id
        WHERE r.code = 'teacher'
        ORDER BY u.full_name ASC, c.title ASC
    SQL);

    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
    $grouped = [];

    foreach ($rows as $row) {
        // Agrupa por docente para evitar duplicar tarjeta por curso.
        $teacherId = (int) $row['id'];

        if (!isset($grouped[$teacherId])) {
            $grouped[$teacherId] = [
                'id' => $teacherId,
                'full_name' => (string) $row['full_name'],
                'email' => (string) $row['email'],
                'courses' => [],
            ];
        }

        if (!empty($row['course_title'])) {
            $grouped[$teacherId]['courses'][] = [
                'title' => (string) $row['course_title'],
                'slug' => (string) ($row['course_slug'] ?? ''),
                'is_published' => (int) ($row['is_published'] ?? 0) === 1,
            ];
        }
    }

    foreach ($grouped as $teacher) {
        if ($teacher['courses'] !== []) {
            // Elimina cursos repetidos por combinacion titulo/slug.
            $unique = [];
            foreach ($teacher['courses'] as $course) {
                $key = $course['title'] . '|' . $course['slug'];
                $unique[$key] = $course;
            }
            $teacher['courses'] = array_values($unique);
        }

        $teachers[] = $teacher;
    }
} catch (Throwable) {
    // Mensaje amigable para el usuario ante error de carga.
    $loadError = 'No se pudo cargar el listado de profesores en este momento.';
}

require __DIR__ . '/../app/views/layouts/header.php';
require __DIR__ . '/../app/views/home/profesores.php';
require __DIR__ . '/../app/views/layouts/footer.php';
