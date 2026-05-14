<?php

declare(strict_types=1);

require __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/index.php';

// Configuracion base de la landing y contenedores para datos dinamicos.
$pageTitle = 'SITECRAFT | Cursos';
$currentPage = 'home';
$pageCss = ['assets/css/pages/home/landing.css'];
$courses = [];
$courseLoadError = null;
$teacherLeadData = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'specialty' => '',
    'experience' => '',
    'message' => '',
];
$teacherLeadErrors = [];
$teacherLeadSuccess = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['form_type'] ?? '') === 'teacher_lead') {
    // Normaliza los datos del formulario para evitar validaciones duplicadas.
    $teacherLeadData = [
        'full_name' => trim((string) ($_POST['full_name'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'phone' => trim((string) ($_POST['phone'] ?? '')),
        'specialty' => trim((string) ($_POST['specialty'] ?? '')),
        'experience' => trim((string) ($_POST['experience'] ?? '')),
        'message' => trim((string) ($_POST['message'] ?? '')),
    ];

    if ($teacherLeadData['full_name'] === '') {
        $teacherLeadErrors[] = 'Indica tu nombre completo.';
    }

    if ($teacherLeadData['email'] === '' || !filter_var($teacherLeadData['email'], FILTER_VALIDATE_EMAIL)) {
        $teacherLeadErrors[] = 'Indica un correo electrónico válido.';
    }

    if ($teacherLeadData['phone'] === '') {
        $teacherLeadErrors[] = 'Indica un teléfono de contacto.';
    }

    if ($teacherLeadData['specialty'] === '') {
        $teacherLeadErrors[] = 'Selecciona tu área principal.';
    }

    if ($teacherLeadData['message'] === '' || mb_strlen($teacherLeadData['message']) < 20) {
        $teacherLeadErrors[] = 'Cuéntanos tu propuesta con al menos 20 caracteres.';
    }

    if ($teacherLeadErrors === []) {
        $lead = [
            'lead_id' => 'TL-' . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(3)), 0, 6),
            'created_at' => date('c'),
            'full_name' => $teacherLeadData['full_name'],
            'email' => $teacherLeadData['email'],
            'phone' => $teacherLeadData['phone'],
            'specialty' => $teacherLeadData['specialty'],
            'experience' => $teacherLeadData['experience'],
            'message' => $teacherLeadData['message'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ];

        $leadDir = __DIR__ . '/../queue/pending/teacher-leads';
        if (!is_dir($leadDir)) {
            mkdir($leadDir, 0775, true);
        }

        $leadFile = $leadDir . '/lead-' . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(2)), 0, 4) . '.json';
        $stored = (bool) file_put_contents(
            $leadFile,
            json_encode($lead, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        if ($stored) {
            $teacherLeadSuccess = 'Gracias por tu interés. Te contactaremos para prepararte una propuesta personalizada.';
            $teacherLeadData = [
                'full_name' => '',
                'email' => '',
                'phone' => '',
                'specialty' => '',
                'experience' => '',
                'message' => '',
            ];
        } else {
            $teacherLeadErrors[] = 'No se pudo registrar tu solicitud. Inténtalo de nuevo en unos minutos.';
        }
    }
}

try {
    // Consulta de catálogo público: solo cursos publicados con un profesor visible.
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

    // Normaliza cada fila para que la vista trabaje con un formato unico.
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
    // Mensaje seguro para interfaz si falla la conexion o la consulta.
    $courseLoadError = 'No se han podido cargar los cursos en este momento.';
}

// Clasifica visualmente el curso segun palabras clave del slug.
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

// Define una insignia comercial segun precio del curso.
function courseBadgeFromPrice(float $price): string
{
    return match (true) {
        $price === 0.0 => 'Gratis',
        $price >= 50 => 'Top ventas',
        $price >= 30 => 'Popular',
        default => 'Nuevo',
    };
}

// Formatea precio para mostrar moneda consistente en toda la web.
function formatPrice(float $price, string $currency): string
{
    if ($price === 0.0) {
        return 'Gratis';
    }

    $symbol = strtoupper($currency) === 'EUR' ? '€' : strtoupper($currency);

    return number_format($price, 2, ',', '.') . ' ' . $symbol;
}

require __DIR__ . '/../app/views/layouts/header.php';
require __DIR__ . '/../app/views/home/landing.php';
require __DIR__ . '/../app/views/layouts/footer.php';
