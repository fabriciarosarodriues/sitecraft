<?php

declare(strict_types=1);

require __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/index.php';

// Si ya está autenticado, redirige al dashboard
if (isAuthenticated()) {
    $user = getCurrentUser();
    $redirectUrl = hasRole('teacher') || hasRole('admin')
        ? appUrl('teacher/dashboard.php')
        : appUrl('student/dashboard.php');
    header('Location: ' . $redirectUrl);
    exit;
}

$loginError = '';

// Procesa credenciales y crea la sesión al validar el usuario.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $loginError = 'Por favor, completa todos los campos.';
    } else {
        $user = authenticateUser($email, $password);

        if ($user) {
            startSession($user['id'], $user['email'], $user['full_name'], $user['roles']);

            // Redirige segun rol para llevar al panel correcto.
            if (in_array('teacher', $user['roles'], true) || in_array('admin', $user['roles'], true)) {
                header('Location: ' . appUrl('teacher/dashboard.php'));
            } else {
                header('Location: ' . appUrl('student/dashboard.php'));
            }
            exit;
        } else {
            $loginError = 'Email o contraseña incorrectos.';
        }
    }
}

require __DIR__ . '/../app/views/auth/login.php';
