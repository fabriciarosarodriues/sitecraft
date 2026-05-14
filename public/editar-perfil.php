<?php

declare(strict_types=1);

require __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/index.php';

// Proteccion de acceso: solo usuarios autenticados editan perfil.
requireAuth();
$user = getCurrentUser();

if ($user === null) {
    header('Location: ' . appUrl('login.php'));
    exit;
}

$pageTitle = 'SITECRAFT | Editar perfil';
$currentPage = 'profile';
$pageCss = ['assets/css/pages/auth/profile.css'];

$errors = [];
$successMessage = null;

$formData = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
];

try {
    // Precarga del formulario con datos persistidos del usuario.
    $statement = db()->prepare('SELECT full_name, email, phone FROM users WHERE id = ? LIMIT 1');
    $statement->execute([(int) $user['id']]);
    $dbUser = $statement->fetch(PDO::FETCH_ASSOC);

    if (!is_array($dbUser)) {
        throw new RuntimeException('No se encontró el usuario.');
    }

    $formData = [
        'full_name' => (string) $dbUser['full_name'],
        'email' => (string) $dbUser['email'],
        'phone' => $dbUser['phone'] !== null ? (string) $dbUser['phone'] : '',
    ];
} catch (Throwable) {
    $errors[] = 'No se pudo cargar tu perfil en este momento.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Normaliza campos enviados antes de validar reglas de negocio.
    $formData['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
    $formData['email'] = strtolower(trim((string) ($_POST['email'] ?? '')));
    $formData['phone'] = trim((string) ($_POST['phone'] ?? ''));

    if ($formData['full_name'] === '' || mb_strlen($formData['full_name']) < 3) {
        $errors[] = 'El nombre debe tener al menos 3 caracteres.';
    }

    if (filter_var($formData['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors[] = 'Introduce un correo electrónico válido.';
    }

    if ($formData['phone'] !== '' && mb_strlen($formData['phone']) > 30) {
        $errors[] = 'El teléfono no puede superar 30 caracteres.';
    }

    if ($errors === []) {
        try {
            // Verifica unicidad del email evitando colision con otra cuenta.
            $checkEmail = db()->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
            $checkEmail->execute([$formData['email'], (int) $user['id']]);

            if ($checkEmail->fetch(PDO::FETCH_ASSOC)) {
                $errors[] = 'Este correo ya está en uso por otra cuenta.';
            } else {
                // Actualiza el perfil y sincroniza la sesión del usuario actual.
                $update = db()->prepare(<<<'SQL'
                    UPDATE users
                    SET full_name = ?, email = ?, phone = ?
                    WHERE id = ?
                SQL);

                $update->execute([
                    $formData['full_name'],
                    $formData['email'],
                    $formData['phone'] !== '' ? $formData['phone'] : null,
                    (int) $user['id'],
                ]);

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                $_SESSION['user']['full_name'] = $formData['full_name'];
                $_SESSION['user']['email'] = $formData['email'];
                $_SESSION['user']['phone'] = $formData['phone'] !== '' ? $formData['phone'] : null;

                $successMessage = 'Datos actualizados correctamente.';
            }
        } catch (Throwable) {
            $errors[] = 'No se pudo actualizar el perfil. Inténtalo de nuevo.';
        }
    }
}

require __DIR__ . '/../app/views/layouts/header.php';
require __DIR__ . '/../app/views/auth/edit-profile.php';
require __DIR__ . '/../app/views/layouts/footer.php';
