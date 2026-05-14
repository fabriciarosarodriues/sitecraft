<?php // Landing principal con propuesta de valor, catálogo y secciones informativas. ?>
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span class="eyebrow">Formación online con seguimiento real</span>
                <h1>Encuentra el curso ideal y aprende con profesionales especializados</h1>
                <p class="hero-copy">Explora cursos de distintas áreas, inscríbete, sigue tu progreso, consigue insignias y accede a herramientas personalizadas según cada formación.</p>
                <div class="hero-actions">
                    <a href="<?= htmlspecialchars(appUrl('cursos.php'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark btn-lg">Ver cursos</a>
                    <a href="#como-funciona" class="btn btn-light btn-lg secondary-action">Cómo funciona</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-panel">
                    <div class="hero-panel__card">
                        <span class="hero-panel__label">Acceso inteligente</span>
                        <h2>Una plataforma, varios perfiles</h2>
                        <ul>
                            <li>Acceso como alumno, profesor o administrador.</li>
                            <li>Entrada directa a tus cursos activos o panel docente.</li>
                            <li>Pagos, progreso, badges y chatbot por curso.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="highlight-strip" id="como-funciona">
    <div class="container">
        <div class="row g-3">
            <div class="col-md-4">
                <article class="feature-card">
                    <i class="bi bi-mortarboard-fill"></i>
                    <h3>Aprende a tu ritmo</h3>
                    <p>Accede a tus cursos activos, revisa el progreso y mantén tu racha diaria.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="feature-card">
                    <i class="bi bi-person-workspace"></i>
                    <h3>Profesores conectados</h3>
                    <p>Cada profesional puede gestionar alumnos, seguimiento y métricas de sus cursos.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="feature-card">
                    <i class="bi bi-robot"></i>
                    <h3>Asistencia inteligente</h3>
                    <p>Un chatbot único con módulos por curso acompaña el aprendizaje y resuelve dudas.</p>
                </article>
            </div>
        </div>
    </div>
</section>

<section class="courses-section" id="courses-grid">
    <div class="container">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Catálogo</span>
                <h2>Todos los cursos disponibles</h2>
            </div>
            <a href="<?= htmlspecialchars(appUrl('cursos.php'), ENT_QUOTES, 'UTF-8'); ?>" class="section-link">Ver todos <i class="bi bi-arrow-right"></i></a>
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
                    <div class="col-md-6 col-xl-3">
                        <article class="course-card">
                            <span class="course-card__badge"><?= htmlspecialchars($course['badge'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="course-card__category"><?= htmlspecialchars($course['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <h3><?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p><?= htmlspecialchars($course['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="course-card__meta">
                                <span><i class="bi bi-person"></i> <?= htmlspecialchars($course['teacher'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <strong><?= htmlspecialchars($course['price'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                            <a href="curso.php?slug=<?= urlencode($course['slug']); ?>" class="btn btn-dark w-100">Ver curso</a>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="teachers-section" id="profesores">
    <div class="container">
        <div class="teachers-banner">
            <div>
                <span class="eyebrow">¿Eres profesional?</span>
                <h2>Comparte tu especialidad y crea tu comunidad de alumnos</h2>
                <p>Publica tus cursos, gestiona inscripciones y accede a un panel de profesor con control de alumnos, pagos y actividad.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= htmlspecialchars(appUrl('profesores.php'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-highlight">Ver profesores</a>
                <a href="#quiero-ser-profesor" class="btn btn-highlight">Quiero ser profesor</a>
            </div>
        </div>
    </div>
</section>

<section class="teacher-lead-section" id="quiero-ser-profesor">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-5">
                <span class="eyebrow">Formulario docente</span>
                <h2>Queremos conocerte y prepararte una propuesta</h2>
                <p class="teacher-lead-copy">Completa tus datos y cuéntanos qué tipo de cursos te gustaría impartir. Nuestro equipo se pondrá en contacto contigo para valorar tu perfil y enviarte una propuesta.</p>
                <ul class="teacher-lead-list">
                    <li><i class="bi bi-check2-circle"></i> Contacto directo por correo o teléfono</li>
                    <li><i class="bi bi-check2-circle"></i> Revisión de especialidad y experiencia</li>
                    <li><i class="bi bi-check2-circle"></i> Propuesta adaptada a tu perfil</li>
                </ul>
            </div>
            <div class="col-lg-7">
                <div class="teacher-lead-card">
                    <?php if (!empty($teacherLeadSuccess)): ?>
                        <div class="alert alert-success" role="alert">
                            <?= htmlspecialchars($teacherLeadSuccess, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($teacherLeadErrors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($teacherLeadErrors as $error): ?>
                                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= htmlspecialchars(appUrl('index.php'), ENT_QUOTES, 'UTF-8'); ?>#quiero-ser-profesor" class="row g-3" novalidate>
                        <input type="hidden" name="form_type" value="teacher_lead">
                        <div class="col-md-6">
                            <label for="teacher_full_name" class="form-label">Nombre completo *</label>
                            <input
                                type="text"
                                class="form-control"
                                id="teacher_full_name"
                                name="full_name"
                                value="<?= htmlspecialchars($teacherLeadData['full_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                required
                            >
                        </div>
                        <div class="col-md-6">
                            <label for="teacher_email" class="form-label">Correo electrónico *</label>
                            <input
                                type="email"
                                class="form-control"
                                id="teacher_email"
                                name="email"
                                value="<?= htmlspecialchars($teacherLeadData['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                required
                            >
                        </div>
                        <div class="col-md-6">
                            <label for="teacher_phone" class="form-label">Teléfono *</label>
                            <input
                                type="text"
                                class="form-control"
                                id="teacher_phone"
                                name="phone"
                                value="<?= htmlspecialchars($teacherLeadData['phone'], ENT_QUOTES, 'UTF-8'); ?>"
                                placeholder="Ej: +34 600 123 123"
                                required
                            >
                        </div>
                        <div class="col-md-6">
                            <label for="teacher_specialty" class="form-label">Área principal *</label>
                            <select class="form-select" id="teacher_specialty" name="specialty" required>
                                <option value="">Selecciona una opción</option>
                                <?php foreach (['fitness' => 'Fitness y salud', 'idiomas' => 'Idiomas', 'programacion' => 'Programación', 'marketing' => 'Marketing', 'negocios' => 'Negocios', 'otro' => 'Otra'] as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?= $teacherLeadData['specialty'] === $value ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="teacher_experience" class="form-label">Experiencia docente</label>
                            <input
                                type="text"
                                class="form-control"
                                id="teacher_experience"
                                name="experience"
                                value="<?= htmlspecialchars($teacherLeadData['experience'], ENT_QUOTES, 'UTF-8'); ?>"
                                placeholder="Ej: 4 años impartiendo cursos online"
                            >
                        </div>
                        <div class="col-12">
                            <label for="teacher_message" class="form-label">Cuéntanos tu propuesta *</label>
                            <textarea
                                class="form-control"
                                id="teacher_message"
                                name="message"
                                rows="5"
                                required
                                placeholder="Describe el tipo de cursos que quieres impartir, tu público y disponibilidad para una llamada."
                            ><?= htmlspecialchars($teacherLeadData['message'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-dark btn-lg">Enviar solicitud</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="faq-section" id="faq">
    <div class="container">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Dudas frecuentes</span>
                <h2>Lo principal antes de empezar</h2>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="faq-card">
                    <h3>¿Qué ocurre al iniciar sesión?</h3>
                    <p>La plataforma detecta si tienes cursos activos, si impartes cursos o si puedes elegir entre modo alumno y profesor.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="faq-card">
                    <h3>¿Y si no he pagado?</h3>
                    <p>Se mostrará un aviso de pago pendiente y un acceso directo al área de pagos correspondiente.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="faq-card">
                    <h3>¿El chatbot está en todos los cursos?</h3>
                    <p>Hay un chatbot único en la plataforma, pero cada curso puede activar su propio módulo o configuración específica.</p>
                </div>
            </div>
        </div>
    </div>
</section>
