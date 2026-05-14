<?php // Directorio publico de profesores y cursos asociados. ?>
<section class="hero-section">
    <div class="container">
        <span class="eyebrow">Equipo docente</span>
        <h1>Profesores de SITECRAFT</h1>
        <p class="hero-copy">Consulta todos los profesores registrados y los cursos que imparten actualmente en la plataforma.</p>
    </div>
</section>

<section class="courses-section" id="profesores-listado">
    <div class="container">
        <?php if (!empty($loadError)): ?>
            <div class="catalog-message catalog-message--error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= htmlspecialchars($loadError, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php elseif (empty($teachers)): ?>
            <div class="catalog-message">
                <i class="bi bi-people-fill"></i>
                <span>No hay profesores disponibles en este momento.</span>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($teachers as $teacher): ?>
                    <div class="col-md-6 col-xl-4">
                        <?php
                            $publishedCourses = array_values(array_filter(
                                $teacher['courses'],
                                static fn(array $course): bool => (bool) ($course['is_published'] ?? false)
                            ));
                            $coursesToShow = $publishedCourses !== [] ? $publishedCourses : $teacher['courses'];
                            $firstCourseSlug = $coursesToShow[0]['slug'] ?? '';
                            $teacherActionUrl = $firstCourseSlug !== ''
                                ? appUrl('curso.php?slug=' . urlencode($firstCourseSlug))
                                : appUrl('cursos.php');
                        ?>
                        <article class="course-card teacher-directory-card">
                            <span class="course-card__badge"><?= count($coursesToShow); ?> curso<?= count($coursesToShow) === 1 ? '' : 's'; ?></span>
                            <span class="course-card__category">Docente</span>
                            <h3><?= htmlspecialchars($teacher['full_name'], ENT_QUOTES, 'UTF-8'); ?></h3>

                            <?php if (!empty($coursesToShow)): ?>
                                <p class="teacher-directory-card__summary">Especialista en:</p>
                                <ul class="teacher-directory-card__courses">
                                    <?php foreach ($coursesToShow as $course): ?>
                                        <li>
                                            <?php if (!empty($course['slug']) && $course['is_published']): ?>
                                                <a href="<?= htmlspecialchars(appUrl('curso.php?slug=' . urlencode($course['slug'])), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            <?php else: ?>
                                                <span><?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="teacher-directory-card__empty">Aún no tiene cursos asignados.</p>
                            <?php endif; ?>

                            <div class="course-card__meta teacher-directory-card__meta">
                                <span><i class="bi bi-envelope"></i> <?= htmlspecialchars($teacher['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>

                            <a href="<?= htmlspecialchars($teacherActionUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark w-100">Ver cursos</a>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
