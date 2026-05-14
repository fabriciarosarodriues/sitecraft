<?php // Vista de perfil: muestra datos y accesos rápidos del usuario autenticado. ?>
<?php if (!$user): ?>
    <div class="container my-5">
        <div class="alert alert-warning">No tienes acceso a esta página.</div>
    </div>
<?php else: ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">

            <!-- Encabezado del perfil -->
            <div class="profile-header mb-5">
                <div class="profile-header__avatar">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="profile-header__info">
                    <h1 class="profile-header__name"><?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p class="profile-header__email"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <div class="profile-header__roles">
                        <?php foreach ($user['roles'] as $role): ?>
                            <?php
                                $roleMap = [
                                    'admin' => ['label' => 'Administrador', 'class' => 'bg-danger'],
                                    'teacher' => ['label' => 'Profesor', 'class' => 'bg-primary'],
                                    'student' => ['label' => 'Alumno', 'class' => 'bg-success'],
                                ];
                                $roleDisplay = $roleMap[$role] ?? ['label' => ucfirst($role), 'class' => 'bg-secondary'];
                            ?>
                            <span class="badge <?= $roleDisplay['class']; ?>"><?= htmlspecialchars($roleDisplay['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de información -->
            <div class="profile-card">
                <h2 class="profile-card__title">
                    <i class="bi bi-info-circle me-2"></i>Información Personal
                </h2>

                <div class="profile-info">
                    <div class="profile-info__row">
                        <div class="profile-info__label">Nombre completo</div>
                        <div class="profile-info__value"><?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>

                    <div class="profile-info__row">
                        <div class="profile-info__label">Email</div>
                        <div class="profile-info__value"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>

                    <?php if (!empty($user['phone'])): ?>
                        <div class="profile-info__row">
                            <div class="profile-info__label">Teléfono</div>
                            <div class="profile-info__value"><?= htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="profile-info__row">
                        <div class="profile-info__label">Miembro desde</div>
                        <div class="profile-info__value">
                            <?php
                                $createdAt = $user['created_at'] ?? null;
                                if (is_string($createdAt) && $createdAt !== '') {
                                    $formattedDate = date('d/m/Y', strtotime($createdAt));
                                    echo htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8');
                                } else {
                                    echo htmlspecialchars(date('d/m/Y'), ENT_QUOTES, 'UTF-8');
                                }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="profile-actions">
                    <a href="<?= htmlspecialchars(appUrl('editar-perfil.php'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary">
                        <i class="bi bi-pencil me-2"></i>Editar perfil
                    </a>
                    <button type="button" class="btn btn-outline-primary" disabled title="Disponible próximamente">
                        <i class="bi bi-key me-2"></i>Cambiar contraseña (próximamente)
                    </button>
                </div>
            </div>

            <!-- Tarjeta de acceso rápido -->
            <div class="profile-quick-access mt-4">
                <h3 class="profile-quick-access__title">
                    <i class="bi bi-speedometer2 me-2"></i>Acceso Rápido
                </h3>
                <div class="row g-3">
                    <?php if (hasRole('teacher') || hasRole('admin')): ?>
                        <div class="col-md-6">
                            <a href="<?= htmlspecialchars(appUrl('teacher/dashboard.php'), ENT_QUOTES, 'UTF-8'); ?>" class="quick-access-card">
                                <i class="bi bi-easel"></i>
                                <span>Panel de Profesor</span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if (hasRole('student')): ?>
                        <div class="col-md-6">
                            <a href="<?= htmlspecialchars(appUrl('student/dashboard.php'), ENT_QUOTES, 'UTF-8'); ?>" class="quick-access-card">
                                <i class="bi bi-book"></i>
                                <span>Mis Cursos</span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if (hasRole('admin')): ?>
                        <div class="col-md-6">
                            <a href="<?= htmlspecialchars(appUrl('admin/dashboard.php'), ENT_QUOTES, 'UTF-8'); ?>" class="quick-access-card">
                                <i class="bi bi-gear"></i>
                                <span>Panel Admin</span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-6">
                        <a href="<?= htmlspecialchars(appUrl('cursos.php'), ENT_QUOTES, 'UTF-8'); ?>" class="quick-access-card">
                            <i class="bi bi-collection-play"></i>
                            <span>Ver Cursos</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Botón de cerrar sesión -->
            <div class="profile-logout mt-5">
                <a href="<?= htmlspecialchars(appUrl('logout.php'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-danger w-100">
                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                </a>
            </div>

        </div>
    </div>
</div>

<?php endif; ?>
