<?php

declare(strict_types=1);

require __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/index.php';

// Requiere sesión activa para consultar datos personales.
requireAuth();
$user = getCurrentUser();

if ($user !== null) {
	try {
		// Refresca datos desde BD para mostrar información actualizada.
		$statement = db()->prepare('SELECT full_name, email, phone, created_at FROM users WHERE id = ? LIMIT 1');
		$statement->execute([(int) $user['id']]);
		$dbUser = $statement->fetch(PDO::FETCH_ASSOC);

		if (is_array($dbUser)) {
			$user['full_name'] = (string) $dbUser['full_name'];
			$user['email'] = (string) $dbUser['email'];
			$user['phone'] = $dbUser['phone'] !== null ? (string) $dbUser['phone'] : null;
			$user['created_at'] = (string) $dbUser['created_at'];

			if (session_status() === PHP_SESSION_NONE) {
				session_start();
			}

			// Sincroniza la sesión para reflejar los cambios de perfil al instante.
			$_SESSION['user']['full_name'] = $user['full_name'];
			$_SESSION['user']['email'] = $user['email'];
			$_SESSION['user']['phone'] = $user['phone'];
			$_SESSION['user']['created_at'] = $user['created_at'];
		}
	} catch (Throwable) {
		// Mantener datos de sesión si falla la consulta puntual.
	}
}

$pageTitle = 'SITECRAFT | Mi Perfil';
$currentPage = 'profile';
$pageCss = ['assets/css/pages/auth/profile.css'];

require __DIR__ . '/../app/views/layouts/header.php';
require __DIR__ . '/../app/views/auth/profile.php';
require __DIR__ . '/../app/views/layouts/footer.php';
