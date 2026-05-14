<?php

declare(strict_types=1);

require __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/index.php';

// Estado inicial del formulario de soporte.
$pageTitle = 'SITECRAFT | Ayuda y soporte';
$currentPage = 'help';
$pageCss = [];

$formData = [
    'full_name' => '',
    'email' => '',
    'course_name' => '',
    'module_lesson' => '',
    'issue_type' => '',
    'message' => '',
];

$errors = [];
$successMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura y normaliza los campos enviados desde la vista.
    $formData = [
        'full_name' => trim((string) ($_POST['full_name'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'course_name' => trim((string) ($_POST['course_name'] ?? '')),
        'module_lesson' => trim((string) ($_POST['module_lesson'] ?? '')),
        'issue_type' => trim((string) ($_POST['issue_type'] ?? '')),
        'message' => trim((string) ($_POST['message'] ?? '')),
    ];

    if ($formData['full_name'] === '') {
        $errors[] = 'Debes indicar tu nombre.';
    }

    if ($formData['email'] === '' || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Debes indicar un correo electrónico válido.';
    }

    if ($formData['issue_type'] === '') {
        $errors[] = 'Selecciona el tipo de incidencia.';
    }

    if ($formData['message'] === '' || mb_strlen($formData['message']) < 10) {
        $errors[] = 'Describe la incidencia con al menos 10 caracteres.';
    }

    if ($errors === []) {
        // Construye el ticket con metadatos mínimos para trazabilidad.
        $ticket = [
            'ticket_id' => 'SC-' . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(3)), 0, 6),
            'created_at' => date('c'),
            'full_name' => $formData['full_name'],
            'email' => $formData['email'],
            'course_name' => $formData['course_name'],
            'module_lesson' => $formData['module_lesson'],
            'issue_type' => $formData['issue_type'],
            'message' => $formData['message'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ];

        $ticketDir = __DIR__ . '/../queue/pending/support';
        if (!is_dir($ticketDir)) {
            // Crea la cola local de soporte si aún no existe.
            mkdir($ticketDir, 0775, true);
        }

        $ticketFile = $ticketDir . '/ticket-' . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(2)), 0, 4) . '.json';
        $stored = (bool) file_put_contents(
            $ticketFile,
            json_encode($ticket, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        if ($stored) {
            // Reinicia formulario tras guardar ticket correctamente.
            $successMessage = 'Hemos recibido tu solicitud. El equipo de soporte la revisará en horario de atención.';
            $formData = [
                'full_name' => '',
                'email' => '',
                'course_name' => '',
                'module_lesson' => '',
                'issue_type' => '',
                'message' => '',
            ];
        } else {
            $errors[] = 'No se pudo registrar la incidencia. Inténtalo de nuevo en unos minutos.';
        }
    }
}

require __DIR__ . '/../app/views/layouts/header.php';
require __DIR__ . '/../app/views/support/ayuda.php';
require __DIR__ . '/../app/views/layouts/footer.php';
