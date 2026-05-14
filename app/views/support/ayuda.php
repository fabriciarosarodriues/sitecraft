<?php // Formulario de soporte para incidencias funcionales y técnicas. ?>
<section class="container my-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h3 mb-3">Formulario de ayuda</h1>
                    <p class="text-muted mb-4">Usa este formulario para incidencias de curso, acceso o pagos. Cuanta más información envíes, más rápido podremos ayudarte.</p>

                    <?php if (!empty($successMessage)): ?>
                        <div class="alert alert-success" role="alert">
                            <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= htmlspecialchars(appUrl('ayuda.php'), ENT_QUOTES, 'UTF-8'); ?>" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label">Nombre completo *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($formData['full_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Correo electrónico *</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($formData['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="course_name" class="form-label">Nombre del curso</label>
                                <input type="text" class="form-control" id="course_name" name="course_name" value="<?= htmlspecialchars($formData['course_name'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej: Entrenador Personal Nivel I">
                            </div>
                            <div class="col-md-6">
                                <label for="module_lesson" class="form-label">Módulo / lección afectada</label>
                                <input type="text" class="form-control" id="module_lesson" name="module_lesson" value="<?= htmlspecialchars($formData['module_lesson'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej.: Bloque 2 - Lección 3">
                            </div>
                            <div class="col-12">
                                <label for="issue_type" class="form-label">Tipo de incidencia *</label>
                                <select class="form-select" id="issue_type" name="issue_type" required>
                                    <option value="">Selecciona una opción</option>
                                    <?php foreach (['acceso' => 'Acceso o sesión', 'curso' => 'Contenido del curso', 'pagos' => 'Pago o matrícula', 'tecnico' => 'Error técnico', 'otro' => 'Otro'] as $value => $label): ?>
                                        <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?= $formData['issue_type'] === $value ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="message" class="form-label">Descripción de la incidencia *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required placeholder="Describe qué pasó, en qué página y el mensaje de error si apareció."><?= htmlspecialchars($formData['message'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-dark">Enviar incidencia</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Canales de contacto</h2>
                    <p class="mb-2"><strong>Correo:</strong> <a href="mailto:soporte@sitecraft.local">soporte@sitecraft.local</a></p>
                    <p class="mb-2"><strong>Teléfono:</strong> <a href="tel:+34900100957">900 100 957</a></p>
                    <p class="mb-0"><strong>Horario:</strong> Lunes a viernes de 09:00 a 18:00</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Para resolver más rápido</h2>
                    <ul class="mb-0 ps-3">
                        <li>Nombre del curso</li>
                        <li>Módulo y lección afectados</li>
                        <li>Tipo de incidencia</li>
                        <li>Fecha y hora aproximada</li>
                        <li>Captura de pantalla</li>
                        <li>Navegador y dispositivo usados</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
