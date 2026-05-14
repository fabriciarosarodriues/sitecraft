<?php
// Devuelve el mensaje de inscripción adecuado según el código recibido por URL.
function resolveEnrollmentErrorMessage(?string $errorCode): string
{
    $errorMessages = [
        'datos_incompletos' => 'Por favor, completa todos los datos requeridos.',
        'curso_no_encontrado' => 'El curso no existe o no está disponible.',
        'ya_inscrito' => 'Ya estás inscrito en este curso.',
        'metodo_pago_requerido' => 'Debes seleccionar un método de pago.',
        'error_sistema' => 'Ocurrió un error durante la inscripción. Inténtalo de nuevo.',
    ];

    return $errorMessages[$errorCode ?? ''] ?? 'Ocurrió un error durante la inscripción.';
}

// Selecciona la primera imagen y el primer vídeo disponibles para la cabecera del curso.
function resolveCourseCoverMedia(array $media): array
{
    $coverImage = null;
    $coverVideo = null;

    foreach ($media as $mediaItem) {
        if ($coverImage === null && $mediaItem['type'] === 'image') {
            $coverImage = $mediaItem;
        }

        if ($coverVideo === null && $mediaItem['type'] === 'video') {
            $coverVideo = $mediaItem;
        }
    }

    return [$coverImage, $coverVideo];
}
?>

<?php if ($loadError): ?>
<section class="course-error container py-5">
    <div class="alert alert-warning d-flex align-items-center gap-3">
        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
        <div>
            <strong>Curso no disponible</strong>
            <p class="mb-0"><?= htmlspecialchars($loadError, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    </div>
    <a href="index.php" class="btn btn-dark mt-3"><i class="bi bi-arrow-left"></i> Volver al catálogo</a>
</section>
<?php return; endif; ?>

<?php
// Mensajes de inscripción
$enrollStatus = $_GET['enroll'] ?? null;
$enrollMessage = $_GET['enroll_message'] ?? null;
$enrollmentId = (int) ($_GET['enrollment_id'] ?? 0);
?>

<?php if ($enrollStatus === 'success'): ?>
    <div class="container mt-3 mb-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <strong>¡Inscripción completada!</strong> Bienvenido al curso. Accede a tu <a href="<?= htmlspecialchars(appUrl('student/dashboard.php'), ENT_QUOTES, 'UTF-8'); ?>">panel de alumno</a> para empezar a estudiar.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php elseif ($enrollStatus === 'pending_payment'): ?>
    <div class="container mt-3 mb-3">
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Inscripción pendiente de pago</strong> Tu solicitud de inscripción ha sido registrada. Por favor, completa el pago para acceder al curso.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php elseif ($enrollStatus === 'error'): ?>
    <div class="container mt-3 mb-3">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <strong>Error en la inscripción</strong>
            <?= htmlspecialchars(resolveEnrollmentErrorMessage(is_string($enrollMessage) ? $enrollMessage : null), ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<?php
// Resuelve la portada visual que se mostrará en la tarjeta lateral.
[$coverImage, $coverVideo] = resolveCourseCoverMedia($media);

$mainTeacher = !empty($teachers) ? $teachers[0]['full_name'] : 'Instructor SITECRAFT';
$categoryList = implode(' · ', $categories);
?>

<!-- ── BREADCRUMB ───────────────────────────────────────────────────────────── -->
<nav class="course-breadcrumb" aria-label="Breadcrumb">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="index.php#courses-grid">Cursos</a></li>
            <?php if ($categoryList): ?>
                <li class="breadcrumb-item text-muted"><?= htmlspecialchars($categories[0] ?? '', ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></li>
        </ol>
    </div>
</nav>

<!-- ── HERO DEL CURSO ───────────────────────────────────────────────────────── -->
<section class="course-hero">
    <div class="container">
        <div class="row g-5 align-items-start">

            <!-- Columna izquierda: info principal -->
            <div class="col-lg-7">
                <?php if ($categoryList): ?>
                    <div class="course-hero__cats mb-2">
                        <?php foreach ($categories as $categoryName): ?>
                            <span class="badge-category"><?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <h1 class="course-hero__title"><?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <p class="course-hero__desc"><?= htmlspecialchars($course['description'], ENT_QUOTES, 'UTF-8'); ?></p>

                <div class="course-hero__meta">
                    <span><i class="bi bi-person-circle"></i> <?= htmlspecialchars($mainTeacher, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if ($course['duration_days']): ?>
                        <span><i class="bi bi-calendar3"></i> <?= (int) $course['duration_days']; ?> días de acceso</span>
                    <?php endif; ?>
                    <span><i class="bi bi-collection-play"></i> <?= $totalLessons; ?> lecciones</span>
                    <span><i class="bi bi-clock"></i> <?= $totalHours; ?> horas</span>
                </div>

                <!-- Stats strip -->
                <div class="course-stats-strip">
                    <div class="course-stat">
                        <span class="course-stat__value"><?= count($modules); ?></span>
                        <span class="course-stat__label">Bloques</span>
                    </div>
                    <div class="course-stat">
                        <span class="course-stat__value"><?= $totalLessons; ?></span>
                        <span class="course-stat__label">Lecciones</span>
                    </div>
                    <div class="course-stat">
                        <span class="course-stat__value"><?= $totalHours; ?>h</span>
                        <span class="course-stat__label">Contenido</span>
                    </div>
                    <div class="course-stat">
                        <span class="course-stat__value"><?= (int) $course['duration_days']; ?></span>
                        <span class="course-stat__label">Días acceso</span>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: tarjeta de compra -->
            <div class="col-lg-5">
                <div class="course-purchase-card">

                    <!-- Imagen/vídeo de portada -->
                    <?php if ($coverImage): ?>
                        <div class="course-purchase-card__cover">
                            <img
                                src="<?= htmlspecialchars(ltrim($coverImage['url'], '/'), ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?= htmlspecialchars($coverImage['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                loading="lazy"
                            >
                            <?php if ($coverVideo): ?>
                                <button class="course-purchase-card__play" aria-label="Ver vídeo de presentación" data-video="<?= htmlspecialchars(ltrim($coverVideo['url'], '/'), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="bi bi-play-circle-fill"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="course-purchase-card__body">
                        <div class="course-purchase-card__price">
                            <?= htmlspecialchars(formatPrice((float) $course['price'], $course['currency']), ENT_QUOTES, 'UTF-8'); ?>
                        </div>

                        <button type="button" class="btn btn-highlight w-100 btn-lg mb-2" data-bs-toggle="modal" data-bs-target="#enrollModal" data-course-id="<?= (int) $course['id']; ?>" data-course-slug="<?= htmlspecialchars($course['slug'], ENT_QUOTES, 'UTF-8'); ?>" data-course-title="<?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="bi bi-bag-check-fill"></i> Inscribirme ahora
                        </button>
                        <a href="#programa" class="btn btn-outline-dark w-100">
                            <i class="bi bi-list-ul"></i> Ver programa completo
                        </a>

                        <ul class="course-purchase-card__includes mt-3">
                            <li><i class="bi bi-check2-circle"></i> Acceso durante <?= (int) $course['duration_days']; ?> días</li>
                            <li><i class="bi bi-check2-circle"></i> <?= $totalLessons; ?> lecciones en vídeo y recursos</li>
                            <li><i class="bi bi-check2-circle"></i> Tutor personal asignado</li>
                            <li><i class="bi bi-check2-circle"></i> Asistente IA por curso</li>
                            <li><i class="bi bi-check2-circle"></i> Certificado de finalización</li>
                            <li><i class="bi bi-check2-circle"></i> PDFs y materiales descargables</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ── PROGRAMA DEL CURSO ────────────────────────────────────────────────────── -->
<section class="course-program" id="programa">
    <div class="container">
        <div class="section-heading mb-4">
            <div>
                <span class="eyebrow">Contenido</span>
                <h2>Programa del curso</h2>
            </div>
            <span class="section-meta"><?= count($modules); ?> bloques · <?= $totalLessons; ?> lecciones · <?= $totalHours; ?>h de contenido</span>
        </div>

        <?php if (empty($modules)): ?>
            <p class="text-muted">El programa estará disponible próximamente.</p>
        <?php else: ?>
            <div class="accordion program-accordion" id="programAccordion">
                <?php foreach ($modules as $moduleIndex => $moduleItem): ?>
                    <?php
                        $moduleCollapseId = 'mod-' . $moduleItem['id'];
                        $moduleHeaderId = 'head-' . $moduleItem['id'];
                        $isFirstModule = $moduleIndex === 0;
                    ?>
                    <div class="accordion-item program-item">
                        <h3 class="accordion-header" id="<?= $moduleHeaderId; ?>">
                            <button
                                class="accordion-button program-item__btn <?= $isFirstModule ? '' : 'collapsed'; ?>"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#<?= $moduleCollapseId; ?>"
                                aria-expanded="<?= $isFirstModule ? 'true' : 'false'; ?>"
                                aria-controls="<?= $moduleCollapseId; ?>"
                            >
                                <span class="program-item__title"><?= htmlspecialchars($moduleItem['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="program-item__meta">
                                    <?= count($moduleItem['lessons']); ?> lecciones
                                    <?php if ($moduleItem['total_min'] > 0): ?>
                                        · <?= round($moduleItem['total_min'] / 60, 1); ?>h
                                    <?php endif; ?>
                                </span>
                            </button>
                        </h3>
                        <div id="<?= $moduleCollapseId; ?>" class="accordion-collapse collapse <?= $isFirstModule ? 'show' : ''; ?>" aria-labelledby="<?= $moduleHeaderId; ?>" data-bs-parent="#programAccordion">
                            <div class="accordion-body p-0">
                                <ul class="lesson-list">
                                    <?php foreach ($moduleItem['lessons'] as $lessonItem): ?>
                                        <li class="lesson-item <?= $lessonItem['is_free_preview'] ? 'lesson-item--free' : ''; ?>">
                                            <span class="lesson-item__icon">
                                                <?php if ($lessonItem['is_free_preview']): ?>
                                                    <i class="bi bi-play-circle text-success"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-lock-fill text-muted"></i>
                                                <?php endif; ?>
                                            </span>
                                            <span class="lesson-item__title"><?= htmlspecialchars($lessonItem['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            <span class="lesson-item__right">
                                                <?php if ($lessonItem['is_free_preview']): ?>
                                                    <span class="badge-free">Vista previa</span>
                                                <?php endif; ?>
                                                <?php if ($lessonItem['duration_minutes']): ?>
                                                    <span class="lesson-item__duration"><i class="bi bi-clock"></i> <?= (int) $lessonItem['duration_minutes']; ?> min</span>
                                                <?php endif; ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ── PROFESORES ────────────────────────────────────────────────────────────── -->
<?php if (!empty($teachers)): ?>
<section class="course-instructors">
    <div class="container">
        <div class="section-heading mb-4">
            <div>
                <span class="eyebrow">Quién imparte</span>
                <h2>Tu<?= count($teachers) > 1 ? 's instructores' : ' instructor/a'; ?></h2>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($teachers as $teacher): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="instructor-card">
                        <div class="instructor-card__avatar">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div class="instructor-card__info">
                            <strong><?= htmlspecialchars($teacher['full_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span>Instructor SITECRAFT</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── CTA DE INSCRIPCIÓN ────────────────────────────────────────────────────── -->
<section class="course-cta" id="inscribirse">
    <div class="container">
        <div class="course-cta__inner">
            <div>
                <h2>¿Listo para empezar?</h2>
                <p>Accede hoy mismo al curso y empieza a formarte con los mejores profesionales.</p>
            </div>
            <div class="course-cta__actions">
                <div class="course-cta__price">
                    <?= htmlspecialchars(formatPrice((float) $course['price'], $course['currency']), ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <button type="button" class="btn btn-highlight btn-lg" data-bs-toggle="modal" data-bs-target="#enrollModal" data-course-id="<?= (int) $course['id']; ?>" data-course-slug="<?= htmlspecialchars($course['slug'], ENT_QUOTES, 'UTF-8'); ?>" data-course-title="<?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?>">
                    <i class="bi bi-bag-check-fill"></i> Inscribirme ahora
                </button>
                <a href="index.php#courses-grid" class="btn btn-outline-light">
                    Ver más cursos
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ── MODAL DE INSCRIPCIÓN ────────────────────────────────────────────────── -->
<div class="modal fade" id="enrollModal" tabindex="-1" aria-labelledby="enrollModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header auth-modal__header">
                <h5 class="modal-title" id="enrollModalLabel">
                    <i class="bi bi-bag-check-fill me-2"></i>Inscribirse al curso
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body -->
            <div class="modal-body auth-modal__body">
                <?php if (!$user): ?>
                    <!-- No autenticado -->
                    <div class="text-center py-3">
                        <i class="bi bi-exclamation-circle text-warning" style="font-size: 3rem;"></i>
                        <h6 class="mt-3 mb-3">Inicia sesión o regístrate</h6>
                        <p class="text-muted">Para inscribirte en este curso necesitas tener una cuenta.</p>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-highlight" data-bs-dismiss="modal" onclick="document.querySelector('#loginModal-trigger')?.click()">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar sesión
                        </button>
                        <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal" onclick="document.querySelector('#registerModal-trigger')?.click()">
                            <i class="bi bi-person-plus me-1"></i>Registrarse
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Autenticado -->
                    <form id="enrollForm" method="POST" action="<?= htmlspecialchars(appUrl('inscribirse.php'), ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="course_id" value="<?= (int) $course['id']; ?>">
                        <input type="hidden" name="course_slug" value="<?= htmlspecialchars($course['slug'], ENT_QUOTES, 'UTF-8'); ?>">

                        <!-- Resumen del curso -->
                        <div class="enroll-summary mb-4 p-3" style="background: #f8ffe0; border-radius: 0.5rem;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0"><?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></h6>
                            </div>
                            <div class="text-muted small mb-2">
                                <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($mainTeacher, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div class="d-flex gap-3 small text-muted">
                                <span><i class="bi bi-collection-play"></i> <?= $totalLessons; ?> lecciones</span>
                                <span><i class="bi bi-clock"></i> <?= $totalHours; ?>h</span>
                            </div>
                        </div>

                        <!-- Datos del usuario -->
                        <div class="mb-3">
                            <label for="enrollName" class="form-label">Nombre completo</label>
                            <input type="text" class="form-control" id="enrollName" value="<?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?>" disabled>
                        </div>

                        <div class="mb-3">
                            <label for="enrollEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="enrollEmail" value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>" disabled>
                        </div>

                        <!-- Método de pago (si es de pago) -->
                        <?php if ((float) $course['price'] > 0): ?>
                            <div class="mb-3">
                                <label for="enrollMethod" class="form-label">Método de pago</label>
                                <select class="form-select" id="enrollMethod" name="payment_method" required>
                                    <option value="">-- Selecciona un método --</option>
                                    <option value="card">Tarjeta de crédito/débito</option>
                                    <option value="transfer">Transferencia bancaria</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="bizum">Bizum</option>
                                </select>
                            </div>

                            <div class="enroll-price mb-3 p-2" style="background: #fff3cd; border-radius: 0.5rem; text-align: center;">
                                <small class="text-muted">Monto a pagar:</small>
                                <div style="font-size: 1.5rem; font-weight: 700; color: #050505;">
                                    <?= htmlspecialchars(formatPrice((float) $course['price'], $course['currency']), ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info small mb-3">
                                <i class="bi bi-check-circle me-1"></i>Este curso es gratuito. Podrás empezar a estudiar inmediatamente.
                            </div>
                        <?php endif; ?>

                        <!-- Términos -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="enrollTerms" name="accept_terms" required>
                            <label class="form-check-label small" for="enrollTerms">
                                Acepto los términos y condiciones del curso
                            </label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-highlight btn-lg">
                                <i class="bi bi-bag-check-fill me-1"></i>Confirmar inscripción
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Llenar datos de curso al abrir modal
    document.getElementById('enrollModal')?.addEventListener('show.bs.modal', function (e) {
        const trigger = e.relatedTarget;
        if (trigger) {
            const courseTitle = trigger.getAttribute('data-course-title');
            // Ya están precargados en PHP, pero aquí podrías hacer cualquier ajuste JS
        }
    });
</script>
