<?php // Vista de gestion de un curso docente con alumnos y progreso. ?>
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">

    <!-- Cabecera del curso -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb" class="mb-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= htmlspecialchars(appUrl('teacher/dashboard.php'), ENT_QUOTES, 'UTF-8'); ?>">Mi panel</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?>
                    </li>
                </ol>
            </nav>
            <h1 class="teacher-curso__title"><?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <span class="badge <?= $course['is_published'] ? 'bg-success' : 'bg-secondary'; ?> mb-2">
                <?= $course['is_published'] ? 'Publicado' : 'Borrador'; ?>
            </span>
            <?php if ($course['price'] > 0): ?>
                <span class="badge bg-dark ms-1"><?= number_format((float)$course['price'], 2, ',', '.'); ?> <?= htmlspecialchars($course['currency'], ENT_QUOTES, 'UTF-8'); ?></span>
            <?php else: ?>
                <span class="badge bg-warning text-dark ms-1">Gratuito</span>
            <?php endif; ?>
        </div>
        <div class="col-md-4 text-end d-flex gap-2 justify-content-end flex-wrap">
            <a href="<?= htmlspecialchars(appUrl('curso.php?slug=' . $course['slug']), ENT_QUOTES, 'UTF-8'); ?>"
               class="btn btn-outline-secondary btn-sm" target="_blank">
                <i class="bi bi-eye"></i> Ver página pública
            </a>
            <a href="<?= htmlspecialchars(appUrl('teacher/dashboard.php'), ENT_QUOTES, 'UTF-8'); ?>"
               class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left"></i> Volver al panel
            </a>
        </div>
    </div>

    <!-- Estadísticas del curso -->
    <div class="row mb-5">
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <div class="stat-content">
                    <h3><?= (int)$courseStats['active_students']; ?></h3>
                    <p>Alumnos activos</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-trophy"></i></div>
                <div class="stat-content">
                    <h3><?= (int)$courseStats['completed_students']; ?></h3>
                    <p>Alumnos completados</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-person-plus"></i></div>
                <div class="stat-content">
                    <h3><?= (int)$courseStats['total_enrollments']; ?></h3>
                    <p>Total matriculados</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de alumnos -->
    <div class="row">
        <div class="col-12">
            <div class="teacher-curso__card">
                <div class="teacher-curso__card-header d-flex justify-content-between align-items-center">
                    <h2 class="teacher-curso__card-title">
                        <i class="bi bi-people me-2"></i>Alumnos matriculados
                    </h2>
                    <span class="badge bg-secondary"><?= count($students); ?> alumnos</span>
                </div>
                <div class="teacher-curso__card-body">
                    <?php if (empty($students)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-1"></i> Aún no hay alumnos matriculados en este curso.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Alumno</th>
                                        <th>Email</th>
                                        <th>Estado</th>
                                        <th>Progreso</th>
                                        <th>Inicio</th>
                                        <th>Fin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="teacher-curso__avatar">
                                                        <?= mb_strtoupper(mb_substr($student['full_name'], 0, 1)); ?>
                                                    </div>
                                                    <span><?= htmlspecialchars($student['full_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                            </td>
                                            <td class="text-muted small"><?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <?php
                                                    $statusMap = [
                                                        'active'          => ['label' => 'Activo',     'class' => 'bg-success'],
                                                        'completed'       => ['label' => 'Completado', 'class' => 'bg-primary'],
                                                        'pending_payment' => ['label' => 'Pendiente',  'class' => 'bg-warning text-dark'],
                                                        'paused'          => ['label' => 'Pausado',    'class' => 'bg-secondary'],
                                                        'cancelled'       => ['label' => 'Cancelado',  'class' => 'bg-danger'],
                                                    ];
                                                    $statusDisplay = $statusMap[$student['enrollment_status']] ?? ['label' => $student['enrollment_status'], 'class' => 'bg-secondary'];
                                                ?>
                                                <span class="badge <?= $statusDisplay['class']; ?>"><?= $statusDisplay['label']; ?></span>
                                            </td>
                                            <td style="min-width:120px">
                                                <div class="progress" style="height:8px" title="<?= number_format((float)$student['completion_percent'], 1); ?>%">
                                                    <div class="progress-bar bg-success"
                                                         role="progressbar"
                                                         style="width:<?= min(100, (float)$student['completion_percent']); ?>%"
                                                         aria-valuenow="<?= (float)$student['completion_percent']; ?>"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small class="text-muted"><?= number_format((float)$student['completion_percent'], 1); ?>%</small>
                                            </td>
                                            <td class="small text-muted">
                                                <?= $student['started_at'] ? date('d/m/Y', strtotime($student['started_at'])) : '—'; ?>
                                            </td>
                                            <td class="small text-muted">
                                                <?= $student['ends_at'] ? date('d/m/Y', strtotime($student['ends_at'])) : '—'; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
