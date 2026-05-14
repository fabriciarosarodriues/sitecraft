<?php

declare(strict_types=1);

require __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/index.php';

// Esta ruta solo acepta POST para evitar registros por URL directa.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . appUrl('index.php'));
    exit;
}

$fullName = $_POST['full_name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$passwordConfirmation = $_POST['password_confirmation'] ?? '';
$phone = $_POST['phone'] ?? null;
$returnTo = $_POST['return_to'] ?? appUrl('index.php');

// Valida retorno interno para prevenir redirecciones externas.
$basePrefix = appUrl('');
if (!is_string($returnTo) || $returnTo === '' || !str_starts_with($returnTo, $basePrefix)) {
    $returnTo = appUrl('index.php');
}

$separator = str_contains($returnTo, '?') ? '&' : '?';

// Ejecuta registro y devuelve resultado a la pantalla de origen.
$result = registerUser($fullName, $email, $password, $passwordConfirmation, $phone);
if ($result['ok'] === true) {
    header('Location: ' . $returnTo . $separator . 'register_status=ok');
    exit;
}

$errorMessage = $result['error'] ?? 'No se pudo registrar la cuenta.';
header('Location: ' . $returnTo . $separator . 'register_status=error&register_message=' . rawurlencode($errorMessage) . '&open_modal=register');
exit;
