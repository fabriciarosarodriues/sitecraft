<?php

declare(strict_types=1);

require __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/index.php';

// Verificar si el usuario está autenticado
$user = isAuthenticated() ? getCurrentUser() : null;

// ── Leer y validar slug ──────────────────────────────────────────────────────
$slug = trim((string) ($_GET['slug'] ?? ''));

if ($slug === '') {
    header('Location: index.php');
    exit;
}

// ── Helpers (mismos que index.php) ───────────────────────────────────────────
function formatPrice(float $price, string $currency): string
{
    if ($price === 0.0) {
        return 'Gratis';
    }
    $symbol = strtoupper($currency) === 'EUR' ? '€' : strtoupper($currency);
    return number_format($price, 2, ',', '.') . ' ' . $symbol;
}

// ── Consulta principal del curso ─────────────────────────────────────────────
$course = null;
$teachers = [];
$modules = [];
$media = [];
$categories = [];
$loadError = null;

try {
    $pdo = db();

    // Curso
    $stmt = $pdo->prepare(<<<'SQL'
        SELECT
            c.id,
            c.slug,
            c.title,
            c.description,
            c.price,
            c.currency,
            c.duration_days,
            c.is_published
        FROM courses c
        WHERE c.slug = :slug AND c.is_published = 1
        LIMIT 1
    SQL);
    $stmt->execute([':slug' => $slug]);
    $course = $stmt->fetch() ?: null;

    if ($course === null) {
        header('HTTP/1.1 404 Not Found');
        $loadError = 'Este curso no existe o no está disponible.';
    } else {
        $courseId = (int) $course['id'];

        // Profesores
        $stmtT = $pdo->prepare(<<<'SQL'
            SELECT u.full_name, u.email
            FROM course_teachers ct
            JOIN users u ON u.id = ct.user_id
            WHERE ct.course_id = :cid
        SQL);
        $stmtT->execute([':cid' => $courseId]);
        $teachers = $stmtT->fetchAll();

        // Categorías
        $stmtCat = $pdo->prepare(<<<'SQL'
            SELECT cc.name
            FROM course_category_map cm
            JOIN course_categories cc ON cc.id = cm.category_id
            WHERE cm.course_id = :cid
        SQL);
        $stmtCat->execute([':cid' => $courseId]);
        $categories = array_column($stmtCat->fetchAll(), 'name');

        // Módulos con sus lecciones
        $stmtMod = $pdo->prepare(<<<'SQL'
            SELECT id, title, description, sort_order
            FROM course_modules
            WHERE course_id = :cid
            ORDER BY sort_order ASC
        SQL);
        $stmtMod->execute([':cid' => $courseId]);
        $rawModules = $stmtMod->fetchAll();

        foreach ($rawModules as $mod) {
            $stmtLes = $pdo->prepare(<<<'SQL'
                SELECT id, title, duration_minutes, is_free_preview, sort_order
                FROM course_lessons
                WHERE module_id = :mid
                ORDER BY sort_order ASC
            SQL);
            $stmtLes->execute([':mid' => (int) $mod['id']]);
            $lessons = $stmtLes->fetchAll();

            $totalMinutes = array_sum(array_column($lessons, 'duration_minutes'));

            $modules[] = [
                'id'          => (int) $mod['id'],
                'title'       => $mod['title'],
                'description' => $mod['description'],
                'lessons'     => $lessons,
                'total_min'   => $totalMinutes,
            ];
        }

        // Media de portada del curso (sin lesson_id)
        $stmtMedia = $pdo->prepare(<<<'SQL'
            SELECT type, title, url, thumbnail_url, description
            FROM course_media
            WHERE course_id = :cid AND lesson_id IS NULL
            ORDER BY sort_order ASC
        SQL);
        $stmtMedia->execute([':cid' => $courseId]);
        $media = $stmtMedia->fetchAll();
    }
} catch (Throwable $e) {
    $loadError = 'Error al cargar el curso. Inténtalo de nuevo más tarde.';
}

// ── Totales para el resumen ───────────────────────────────────────────────────
$totalLessons = array_sum(array_map(static fn ($m) => count($m['lessons']), $modules));
$totalHours   = round(array_sum(array_map(static fn ($m) => $m['total_min'], $modules)) / 60, 1);

// ── Variables de página ──────────────────────────────────────────────────────
$pageTitle = isset($course['title'])
    ? 'SITECRAFT | ' . $course['title']
    : 'SITECRAFT | Curso no encontrado';

$currentPage = 'courses';
$pageCss = array_filter([
    'assets/css/pages/courses/detail.css',
    // CSS específico del curso si existe en BD
    !empty($course['css_file']) ? htmlspecialchars($course['css_file'], ENT_QUOTES, 'UTF-8') : null,
]);

require __DIR__ . '/../app/views/layouts/header.php';
require __DIR__ . '/../app/views/courses/detail.php';
require __DIR__ . '/../app/views/layouts/footer.php';
