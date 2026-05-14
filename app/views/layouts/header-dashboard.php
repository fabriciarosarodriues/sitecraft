<?php
$phonePrimary = '900 100 957';
$phoneSecondary = '619 926 324';
$user = getCurrentUser();
$teacherCourseLink = null;

if ($user && (hasRole('teacher') || hasRole('admin'))) {
    try {
        $statement = db()->prepare(<<<'SQL'
            SELECT c.slug
            FROM courses c
            INNER JOIN course_teachers ct ON ct.course_id = c.id
            WHERE ct.user_id = ?
            ORDER BY c.title ASC
            LIMIT 1
        SQL);
        $statement->execute([$user['id']]);
        $teacherCourseSlug = $statement->fetchColumn();

        if (is_string($teacherCourseSlug) && $teacherCourseSlug !== '') {
            $teacherCourseLink = appUrl('teacher/curso.php?slug=' . urlencode($teacherCourseSlug));
        }
    } catch (Throwable) {
        $teacherCourseLink = null;
    }
}

$registerStatus = $_GET['register_status'] ?? null;
$registerMessage = $_GET['register_message'] ?? null;
$openModal = $_GET['open_modal'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'SITECRAFT', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/base/bootstrap.min.css'), ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/icons/bootstrap-icons.css'), ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/base/layout.css'), ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/base/header.css'), ENT_QUOTES, 'UTF-8'); ?>">
    <?php foreach ($pageCss ?? ['assets/css/pages/home/landing.css'] as $cssFile): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl($cssFile), ENT_QUOTES, 'UTF-8'); ?>">
    <?php endforeach; ?>
</head>
<body>
<header class="site-header">
    <div class="topbar">
        <div class="container topbar__inner">
            <div class="topbar__contact">
                <span><i class="bi bi-telephone-fill"></i> <?= htmlspecialchars($phonePrimary, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="divider">|</span>
                <span><i class="bi bi-whatsapp"></i> <?= htmlspecialchars($phoneSecondary, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="topbar__actions">
                <?php if ($user): ?>
                    <div class="user-menu">
                        <span class="user-name"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <a href="<?= htmlspecialchars(appUrl('logout.php'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-dark btn-sm custom-outline">Cerrar sesión</a>
                    </div>
                    <div class="user-menu dropdown">
                        <button class="user-profile-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Abrir perfil">
                            <i class="bi bi-person-circle"></i>
                            <span class="user-name-text"><?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end user-profile-dropdown">
                            <li>
                                <a class="dropdown-item" href="<?= htmlspecialchars(appUrl('perfil.php'), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="bi bi-person me-2"></i>Mi perfil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if ($teacherCourseLink !== null): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= htmlspecialchars($teacherCourseLink, ENT_QUOTES, 'UTF-8'); ?>">
                                        <i class="bi bi-journal-richtext me-2"></i>Panel del curso
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (hasRole('teacher') || hasRole('admin')): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= htmlspecialchars(appUrl('teacher/dashboard.php'), ENT_QUOTES, 'UTF-8'); ?>">
                                        <i class="bi bi-easel me-2"></i>Panel de profesor
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (hasRole('student')): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= htmlspecialchars(appUrl('student/dashboard.php'), ENT_QUOTES, 'UTF-8'); ?>">
                                        <i class="bi bi-book me-2"></i>Mi panel de alumno
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (hasRole('admin')): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= htmlspecialchars(appUrl('admin/dashboard.php'), ENT_QUOTES, 'UTF-8'); ?>">
                                        <i class="bi bi-gear me-2"></i>Admin
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= htmlspecialchars(appUrl('logout.php'), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <button type="button" class="btn btn-outline-dark btn-sm custom-outline" data-bs-toggle="modal" data-bs-target="#loginModal">
                        Iniciar sesión
                    </button>
                    <button type="button" class="btn btn-outline-dark btn-sm custom-outline" data-bs-toggle="modal" data-bs-target="#registerModal">
                        ¡Quiero registrarme!
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($registerStatus === 'ok'): ?>
        <div class="container mt-2">
            <div class="alert alert-success mb-0" role="alert">
                Registro completado. Ya puedes iniciar sesión con tu nuevo usuario.
            </div>
        </div>
    <?php elseif ($registerStatus === 'error' && is_string($registerMessage)): ?>
        <div class="container mt-2">
            <div class="alert alert-danger mb-0" role="alert">
                <?= htmlspecialchars($registerMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="branding-bar">
        <div class="container branding-bar__inner">
            <a href="<?= htmlspecialchars(appUrl('index.php'), ENT_QUOTES, 'UTF-8'); ?>" class="brand-mark text-decoration-none" aria-label="Ir al inicio de SITECRAFT">
                <span class="brand-mark__sitecraft">site</span><span class="brand-mark__accent">craft</span>
                <small>cursos y aprendizaje</small>
            </a>
        </div>
    </div>
</header>

<?php if (!$user): ?>
<div class="modal fade auth-modal" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered auth-modal__dialog">
        <div class="modal-content auth-modal__content">
            <div class="modal-header auth-modal__header">
                <h5 class="modal-title auth-modal__title" id="loginModalLabel">Iniciar sesión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="<?= htmlspecialchars(appUrl('login.php'), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-body auth-modal__body">
                    <p class="auth-modal__intro">Acceso para alumnos y profesores.</p>
                    <div class="mb-3">
                        <label for="modalEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="modalEmail" name="email" placeholder="correo@ejemplo.com" required>
                    </div>
                    <div class="mb-2">
                        <label for="modalPassword" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="modalPassword" name="password" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="modal-footer auth-modal__footer">
                    <button type="button" class="btn btn-outline-secondary auth-modal__secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary auth-modal__primary">Entrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade auth-modal" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered auth-modal__dialog">
        <div class="modal-content auth-modal__content">
            <div class="modal-header auth-modal__header">
                <h5 class="modal-title auth-modal__title" id="registerModalLabel">Crear cuenta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="<?= htmlspecialchars(appUrl('register.php'), ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="return_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? appUrl('index.php'), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-body auth-modal__body">
                    <p class="auth-modal__intro">Registro para nuevos alumnos.</p>
                    <div class="mb-3">
                        <label for="registerName" class="form-label">Nombre completo</label>
                        <input type="text" class="form-control" id="registerName" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerEmail" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="registerEmail" name="email" placeholder="correo@ejemplo.com" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerPhone" class="form-label">Teléfono (opcional)</label>
                        <input type="text" class="form-control" id="registerPhone" name="phone" placeholder="600123123">
                    </div>
                    <div class="mb-2">
                        <label for="registerPassword" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="registerPassword" name="password" minlength="6" required>
                    </div>
                    <div class="mb-2">
                        <label for="registerPasswordConfirmation" class="form-label">Confirmar contraseña</label>
                        <input type="password" class="form-control" id="registerPasswordConfirmation" name="password_confirmation" minlength="6" required>
                    </div>
                </div>
                <div class="modal-footer auth-modal__footer">
                    <button type="button" class="btn btn-outline-secondary auth-modal__secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary auth-modal__primary">Registrarme</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($openModal === 'register'): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalElement = document.getElementById('registerModal');
    if (!modalElement || typeof bootstrap === 'undefined') {
        return;
    }

    var modal = new bootstrap.Modal(modalElement);
    modal.show();
});
</script>
<?php endif; ?>
<?php endif; ?>

<main>
