<?php
// Vista de acceso: muestra el formulario y posibles mensajes de autenticación.
$pageTitle = 'SITECRAFT | Iniciar sesión';
$currentPage = 'login';
$pageCss = ['assets/css/pages/auth/login.css'];

require __DIR__ . '/../layouts/header.php';
?>
<section class="login-page">
    <div class="container">
        <div class="login-container">
            <div class="login-box">
                <div class="login-header">
                    <h1>Iniciar sesión</h1>
                    <p>Accede a tu cuenta para continuar con tus cursos</p>
                </div>

                <?php if (!empty($loginError)): ?>
                    <!-- Mensaje de error de login devuelto por el controlador -->
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i>
                        <?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= htmlspecialchars(appUrl('login.php'), ENT_QUOTES, 'UTF-8'); ?>" class="login-form">
                    <!-- Credenciales principales para autenticación -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input 
                            type="email" 
                            class="form-control form-control-lg" 
                            id="email" 
                            name="email" 
                            placeholder="correo@ejemplo.com"
                            value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            required
                            autofocus
                        >
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input 
                            type="password" 
                            class="form-control form-control-lg" 
                            id="password" 
                            name="password" 
                            placeholder="••••••••"
                            required
                        >
                    </div>

                    <div class="mb-3 form-check">
                        <input 
                            type="checkbox" 
                            class="form-check-input" 
                            id="rememberMe" 
                            name="remember"
                        >
                        <label class="form-check-label" for="rememberMe">
                            Recuérdame
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                        Iniciar sesión
                    </button>
                </form>

                <div class="login-footer">
                    <p class="text-center text-muted mb-0">
                        ¿No tienes cuenta? <a href="#register">Regístrate aquí</a>
                    </p>
                </div>

                <hr class="my-4">

                <div class="test-credentials" style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; font-size: 13px;">
                    <p class="mb-2"><strong>📋 Credenciales de prueba:</strong></p>
                    <p class="mb-1"><small><strong>Profesor:</strong> laura.martin@sitecraft.local</small></p>
                    <p class="mb-1"><small><strong>Alumno:</strong> ana.lopez@sitecraft.local</small></p>
                    <p class="mb-0"><small><strong>Contraseña (todas):</strong> password</small></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
