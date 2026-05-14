<?php // Vista de catálogo: renderiza cursos publicados y estados de carga. ?>
<section class="catalog-hero">
    <div class="container">
        <div class="catalog-hero__content">
            <span class="eyebrow">Catálogo completo</span>
            <h1>Todos los cursos disponibles</h1>
            <p>Explora todas las formaciones publicadas en SITECRAFT y entra en la ficha del curso que mejor encaje contigo.</p>
        </div>
    </div>
</section>

<section class="courses-section courses-section--catalog">
    <div class="container">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Cursos publicados</span>
                <h2>Encuentra tu siguiente formación</h2>
            </div>
            <span class="section-link"><?= count($courses); ?> curso(s) disponibles</span>
        </div>

        <?php if (!empty($courseLoadError)): ?>
            <div class="catalog-message catalog-message--error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= htmlspecialchars($courseLoadError, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php elseif (empty($courses)): ?>
            <div class="catalog-message">
                <i class="bi bi-journal-bookmark-fill"></i>
                <span>Aún no hay cursos publicados. Cuando empieces a crear cursos aparecerán aquí automáticamente.</span>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="course-card course-card--catalog">
                            <span class="course-card__badge"><?= htmlspecialchars($course['badge'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="course-card__category"><?= htmlspecialchars($course['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <h3><?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p><?= htmlspecialchars($course['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="course-card__meta">
                                <span><i class="bi bi-person"></i> <?= htmlspecialchars($course['teacher'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <strong><?= htmlspecialchars($course['price'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                            <a href="<?= htmlspecialchars(appUrl('curso.php?slug=' . $course['slug']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark w-100">Ver curso</a>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
