<?php

declare(strict_types=1);

/**
 * Devuelve la ruta base pública de la app (p.ej. /SITECRAFT/public).
 */
function appBasePath(): string
{
    static $basePath = null;

    if ($basePath !== null) {
        return $basePath;
    }

    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $publicMarker = '/public/';
    $markerPosition = strpos($scriptName, $publicMarker);

    if ($markerPosition !== false) {
        $prefix = substr($scriptName, 0, $markerPosition);
        $basePath = rtrim($prefix . '/public', '/');
        return $basePath;
    }

    $dir = str_replace('\\', '/', dirname($scriptName));
    $dir = $dir === '/' ? '' : rtrim($dir, '/');
    $basePath = $dir;

    return $basePath;
}

/**
 * Construye una URL interna respetando la ruta base de la app.
 */
function appUrl(string $path = ''): string
{
    $basePath = appBasePath();
    $normalizedPath = ltrim($path, '/');

    if ($normalizedPath === '') {
        return $basePath === '' ? '/' : $basePath . '/';
    }

    return $basePath === '' ? '/' . $normalizedPath : $basePath . '/' . $normalizedPath;
}

/**
 * Inicia sesión para el usuario autenticado
 */
function startSession(int $userId, string $email, string $fullName, array $roles = []): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['user'] = [
        'id' => $userId,
        'email' => $email,
        'full_name' => $fullName,
        'roles' => $roles,
    ];

    // Regenerar ID de sesión por seguridad
    session_regenerate_id(true);
}

/**
 * Verifica si el usuario está autenticado
 */
function isAuthenticated(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return isset($_SESSION['user']['id']);
}

/**
 * Obtiene el usuario actual
 */
function getCurrentUser(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return $_SESSION['user'] ?? null;
}

/**
 * Verifica si el usuario tiene un rol específico
 */
function hasRole(string $role): bool
{
    $user = getCurrentUser();
    return $user !== null && in_array($role, $user['roles'], true);
}

/**
 * Cierra la sesión
 */
function logout(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    session_destroy();
    unset($_SESSION);
}

/**
 * Autentica un usuario por email y contraseña
 */
function authenticateUser(string $email, string $password): ?array
{
    try {
        $statement = db()->prepare(<<<'SQL'
            SELECT 
                u.id,
                u.full_name,
                u.email,
                u.password_hash,
                u.is_active,
                GROUP_CONCAT(r.code) as roles
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE u.email = ?
            GROUP BY u.id
        SQL);

        $statement->execute([$email]);
        $user = $statement->fetch();

        if (!$user) {
            return null;
        }

        if (!$user['is_active']) {
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        $roles = $user['roles'] ? explode(',', $user['roles']) : [];

        return [
            'id' => (int) $user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'roles' => $roles,
        ];
    } catch (Throwable $exception) {
        return null;
    }
}

/**
 * Registra un nuevo usuario con rol student.
 *
 * @return array{ok: bool, error?: string, user?: array{id:int,email:string,full_name:string,roles:array<int,string>}}
 */
function registerUser(string $fullName, string $email, string $password, string $passwordConfirmation, ?string $phone = null): array
{
    $normalizedName = trim($fullName);
    $normalizedEmail = strtolower(trim($email));
    $normalizedPhone = $phone !== null ? trim($phone) : null;

    if ($normalizedName === '' || $normalizedEmail === '' || trim($password) === '' || trim($passwordConfirmation) === '') {
        return ['ok' => false, 'error' => 'Completa todos los campos obligatorios.'];
    }

    if ($password !== $passwordConfirmation) {
        return ['ok' => false, 'error' => 'Las contraseñas no coinciden.'];
    }

    if (filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL) === false) {
        return ['ok' => false, 'error' => 'El correo no tiene un formato válido.'];
    }

    if (mb_strlen($password) < 6) {
        return ['ok' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres.'];
    }

    try {
        $pdo = db();

        $checkUser = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $checkUser->execute([$normalizedEmail]);
        if ($checkUser->fetch()) {
            return ['ok' => false, 'error' => 'Este correo ya está registrado.'];
        }

        $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE code = 'student' LIMIT 1");
        $roleStmt->execute();
        $role = $roleStmt->fetch();
        if (!$role) {
            return ['ok' => false, 'error' => 'No se encontró el rol student en la base de datos.'];
        }

        $pdo->beginTransaction();

        $insertUser = $pdo->prepare(<<<'SQL'
            INSERT INTO users (full_name, email, password_hash, phone, is_active)
            VALUES (?, ?, ?, ?, 1)
        SQL);

        $insertUser->execute([
            $normalizedName,
            $normalizedEmail,
            password_hash($password, PASSWORD_DEFAULT),
            $normalizedPhone !== '' ? $normalizedPhone : null,
        ]);

        $userId = (int) $pdo->lastInsertId();

        $insertRole = $pdo->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)');
        $insertRole->execute([$userId, (int) $role['id']]);

        $pdo->commit();

        return [
            'ok' => true,
            'user' => [
                'id' => $userId,
                'email' => $normalizedEmail,
                'full_name' => $normalizedName,
                'roles' => ['student'],
            ],
        ];
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return ['ok' => false, 'error' => 'No se pudo completar el registro. Inténtalo de nuevo.'];
    }
}

/**
 * Redirige a login si no está autenticado
 */
function requireAuth(?string $requiredRole = null): void
{
    if (!isAuthenticated()) {
        header('Location: ' . appUrl('login.php'));
        exit;
    }

    if ($requiredRole !== null && !hasRole($requiredRole)) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Acceso denegado: no tienes permisos para acceder a esta página.';
        exit;
    }
}
