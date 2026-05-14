<?php

declare(strict_types=1);

require __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/index.php';

// Configuración de página y estructura inicial del listado.
$pageTitle = 'SITECRAFT | Catálogo de cursos';
$currentPage = 'courses';
$pageCss = [
    'assets/css/pages/home/landing.css',
    'assets/css/pages/courses/catalog.css',
];
$courses = [];
$courseLoadError = null;

try {
    // Recupera cursos publicados para el catálogo general.
    $statement = db()->query(<<<'SQL'
        SELECT
            c.id,
            c.slug,
            c.title,
            c.description,
            c.price,
            c.currency,
            COALESCE(MIN(u.full_name), 'Profesor pendiente') AS teacher_name
        FROM courses c
        LEFT JOIN course_teachers ct ON ct.course_id = c.id
        LEFT JOIN users u ON u.id = ct.user_id
        WHERE c.is_published = 1
        GROUP BY c.id, c.slug, c.title, c.description, c.price, c.currency
        ORDER BY c.created_at DESC
    SQL);

    // Adapta los datos crudos a un modelo de vista común.
    $courses = array_map(static function (array $course): array {
        return [
            'id' => (int) $course['id'],
            'slug' => $course['slug'],
            'title' => $course['title'],
            'category' => courseCategoryFromSlug($course['slug']),
            'teacher' => $course['teacher_name'],
            'price' => formatPrice((float) $course['price'], $course['currency']),
            'description' => $course['description'] ?: 'Próximamente tendrás más información sobre este curso.',
            'badge' => courseBadgeFromPrice((float) $course['price']),
        ];
    }, $statement->fetchAll());
} catch (Throwable $exception) {
    // Se evita exponer el error técnico al usuario final.
    $courseLoadError = 'No se han podido cargar los cursos en este momento.';
}

// Heurística rápida para etiquetar categoría por slug.
function courseCategoryFromSlug(string $slug): string
{
    $normalizedSlug = strtolower($slug);

    return match (true) {
        str_contains($normalizedSlug, 'fit'), str_contains($normalizedSlug, 'nutri'), str_contains($normalizedSlug, 'entren') => 'Fitness',
        str_contains($normalizedSlug, 'idioma'), str_contains($normalizedSlug, 'english'), str_contains($normalizedSlug, 'ingles') => 'Idiomas',
        str_contains($normalizedSlug, 'program'), str_contains($normalizedSlug, 'web'), str_contains($normalizedSlug, 'code') => 'Programación',
        default => 'General',
    };
}

// Etiqueta promocional usada en tarjetas de curso.
function courseBadgeFromPrice(float $price): string
{
    return match (true) {
        $price === 0.0 => 'Gratis',
        $price >= 50 => 'Top ventas',
        $price >= 30 => 'Popular',
        default => 'Nuevo',
    };
}

// Salida de precio con formato regional para EUR.
function formatPrice(float $price, string $currency): string
{
    if ($price === 0.0) {
        return 'Gratis';
    }

    $symbol = strtoupper($currency) === 'EUR' ? '€' : strtoupper($currency);

    return number_format($price, 2, ',', '.') . ' ' . $symbol;
}

require __DIR__ . '/../app/views/layouts/header.php';
require __DIR__ . '/../app/views/courses/catalog.php';
require __DIR__ . '/../app/views/layouts/footer.php';
