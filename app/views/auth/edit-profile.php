<?php // Vista de edicion de perfil: formulario y feedback de validacion. ?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="profile-card">
                <h1 class="profile-card__title">
                    <i class="bi bi-person-gear me-2"></i>Perfil y configuración personal
                </h1>

                <p class="text-muted mb-4">
                    Desde esta sección puedes mantener actualizados tu nombre, correo electrónico y teléfono para asegurar una comunicación correcta con la plataforma, el tutor y el área administrativa.
                </p>

                <?php if (!empty($successMessage)): ?>
                    <!-- Confirmacion visual tras guardar cambios -->
                    <div class="alert alert-success" role="alert">
                        <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <!-- Lista de errores de validacion del formulario -->
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= htmlspecialchars(appUrl('editar-perfil.php'), ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Nombre completo</label>
                        <input
                            type="text"
                            class="form-control"
                            id="full_name"
                            name="full_name"
                            value="<?= htmlspecialchars($formData['full_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars($formData['email'], ENT_QUOTES, 'UTF-8'); ?>"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label for="phone" class="form-label">Teléfono</label>
                        <input
                            type="text"
                            class="form-control"
                            id="phone"
                            name="phone"
                            maxlength="30"
                            value="<?= htmlspecialchars($formData['phone'], ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="Ej: 600123456"
                        >
                    </div>

                    <div class="profile-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Guardar cambios
                        </button>
                        <a href="<?= htmlspecialchars(appUrl('perfil.php'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left me-2"></i>Volver al perfil
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
