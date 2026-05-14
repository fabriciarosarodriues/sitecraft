<?php
// Layout diferente para el aula: sin el header de marketing, solo barra de curso
require_once __DIR__ . '/../../../app/helpers/index.php';
$user = getCurrentUser();
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
    <?php foreach ($pageCss ?? [] as $cssFile): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl($cssFile), ENT_QUOTES, 'UTF-8'); ?>">
    <?php endforeach; ?>
</head>
<body class="curso-aula">

<!-- Barra superior del aula -->
<nav class="aula-topbar">
    <a class="aula-topbar__back" href="<?= htmlspecialchars(appUrl('student/dashboard.php'), ENT_QUOTES, 'UTF-8'); ?>">
        <i class="bi bi-arrow-left"></i> Mis cursos
    </a>
    <div class="aula-topbar__title">
        <?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <div class="aula-topbar__user">
        <a href="<?= htmlspecialchars(appUrl('student/chatbot.php?slug=' . urlencode((string) $slug)), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-light me-2">
            <i class="bi bi-chat-dots me-1"></i>Chatbot
        </a>
        <i class="bi bi-person-circle"></i>
        <span><?= htmlspecialchars($user['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
</nav>

<?php
// Variables disponibles desde el controlador:
// $course, $modules, $activeLesson, $lessonMedia, $enrollment, $progress, $totalLessons
// $streak, $totalMinutes, $privileges, $completionPctVal
$completionPct = $progress['completion_percent'] ?? 0;
$totalHours   = intdiv($totalMinutes ?? 0, 60);
$remMinutes   = ($totalMinutes ?? 0) % 60;
$unlockedCount = count(array_filter($privileges ?? [], fn($p) => $p['unlocked']));

$videoPosterFallback = 'assets/uploads/courses/entrenador-personal-nivel-i/video-presentacion.png';
$publicRootPath = dirname(__DIR__, 3) . '/public/';

$resolveMediaPath = static function (?string $path, string $fallback = '') use ($publicRootPath): string {
    $normalizedPath = ltrim((string) $path, '/');

    if ($normalizedPath !== '' && is_file($publicRootPath . $normalizedPath)) {
        return '../' . $normalizedPath;
    }

    $normalizedFallback = ltrim($fallback, '/');
    if ($normalizedFallback !== '') {
        return '../' . $normalizedFallback;
    }

    return '';
};
?>

<div class="aula-layout">

    <!-- SIDEBAR: Módulos y Lecciones -->
    <aside class="aula-sidebar" id="aulaSidebar">
        <div class="aula-sidebar__header">
            <span class="aula-sidebar__label">Contenido del curso</span>
            <span class="aula-sidebar__count"><?= $totalLessons; ?> lecciones</span>
        </div>

        <!-- Progreso global -->
        <div class="aula-sidebar__progress">
            <div class="aula-sidebar__progress-text">
                Progreso: <?= number_format((float)$completionPct, 0); ?>%
            </div>
            <div class="progress" style="height:6px;">
                <div class="progress-bar bg-success"
                     role="progressbar"
                     style="width: <?= number_format((float)$completionPct, 0); ?>%"
                     aria-valuenow="<?= number_format((float)$completionPct, 0); ?>"
                     aria-valuemin="0"
                     aria-valuemax="100">
                </div>
            </div>
        </div>

        <!-- Stats: racha + horas -->
        <div class="aula-stats">
            <div class="aula-stat">
                <span class="aula-stat__icon aula-stat__icon--streak">🔥</span>
                <span class="aula-stat__value"><?= (int)($streak ?? 0); ?></span>
                <span class="aula-stat__label"><?= (int)($streak ?? 0) === 1 ? 'día' : 'días'; ?> racha</span>
            </div>
            <div class="aula-stat">
                <span class="aula-stat__icon aula-stat__icon--time"><i class="bi bi-clock-fill"></i></span>
                <span class="aula-stat__value">
                    <?php if ($totalHours > 0): ?><?= $totalHours; ?>h <?= $remMinutes; ?>m<?php else: ?><?= $remMinutes; ?>m<?php endif; ?>
                </span>
                <span class="aula-stat__label">dedicadas</span>
            </div>
            <div class="aula-stat">
                <span class="aula-stat__icon aula-stat__icon--unlock"><i class="bi bi-unlock-fill"></i></span>
                <span class="aula-stat__value"><?= $unlockedCount; ?>/<?= count($privileges ?? []); ?></span>
                <span class="aula-stat__label">logros</span>
            </div>
        </div>

        <!-- Acordeón de módulos -->
        <div class="accordion accordion-flush" id="modulesAccordion">
            <?php foreach ($modules as $moduleIndex => $moduleData): ?>
                <?php
                $isActiveModule = false;
                foreach ($moduleData['lessons'] as $lessonData) {
                    if ((int)$lessonData['id'] === (int)($activeLesson['id'] ?? 0)) {
                        $isActiveModule = true;
                        break;
                    }
                }
                $collapseId = 'modCollapse' . $moduleData['id'];
                ?>
                <div class="accordion-item aula-module">
                    <h2 class="accordion-header">
                        <button class="accordion-button aula-module__btn <?= $isActiveModule ? '' : 'collapsed'; ?>"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#<?= $collapseId; ?>"
                                aria-expanded="<?= $isActiveModule ? 'true' : 'false'; ?>">
                            <span class="aula-module__number"><?= $moduleIndex + 1; ?></span>
                            <span class="aula-module__title"><?= htmlspecialchars($moduleData['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="aula-module__count ms-auto"><?= count($moduleData['lessons']); ?></span>
                        </button>
                    </h2>
                    <div id="<?= $collapseId; ?>"
                         class="accordion-collapse collapse <?= $isActiveModule ? 'show' : ''; ?>"
                         data-bs-parent="#modulesAccordion">
                        <div class="accordion-body p-0">
                            <?php if (empty($moduleData['lessons'])): ?>
                                <p class="aula-lesson aula-lesson--empty">Sin lecciones disponibles</p>
                            <?php else: ?>
                                <?php foreach ($moduleData['lessons'] as $lessonData): ?>
                                    <?php $isActive = (int)$lessonData['id'] === (int)($activeLesson['id'] ?? 0); ?>
                                    <a href="?slug=<?= urlencode($slug); ?>&lesson=<?= (int)$lessonData['id']; ?>"
                                       class="aula-lesson <?= $isActive ? 'aula-lesson--active' : ''; ?>">
                                        <i class="bi bi-play-circle aula-lesson__icon"></i>
                                        <span class="aula-lesson__title"><?= htmlspecialchars($lessonData['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if ($lessonData['duration_minutes']): ?>
                                            <span class="aula-lesson__duration"><?= (int)$lessonData['duration_minutes']; ?> min</span>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Privilegios desbloqueables -->
        <div class="aula-privileges">
            <div class="aula-privileges__header">
                <i class="bi bi-shield-check"></i>
                <span>Privilegios del curso</span>
                <span class="aula-privileges__badge"><?= $unlockedCount; ?>/<?= count($privileges ?? []); ?></span>
            </div>
            <?php foreach ($privileges ?? [] as $privilege): ?>
                <div class="aula-priv <?= $privilege['unlocked'] ? 'aula-priv--unlocked' : 'aula-priv--locked'; ?>">
                    <div class="aula-priv__icon" style="color: <?= $privilege['unlocked'] ? htmlspecialchars($privilege['color'], ENT_QUOTES, 'UTF-8') : '#666'; ?>">
                        <i class="bi <?= htmlspecialchars($privilege['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                    </div>
                    <div class="aula-priv__body">
                        <span class="aula-priv__label"><?= htmlspecialchars($privilege['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php if ($privilege['unlocked']): ?>
                            <span class="aula-priv__status aula-priv__status--ok"><i class="bi bi-check-circle-fill"></i> Desbloqueado</span>
                        <?php else: ?>
                            <span class="aula-priv__status aula-priv__status--locked"><i class="bi bi-lock-fill"></i> <?= htmlspecialchars($privilege['requirement'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="aula-main">

        <?php if ($activeLesson): ?>

            <!-- Banner de racha (si lleva 3+ días) -->
            <?php if (($streak ?? 0) >= 3): ?>
            <div class="aula-streak-banner">
                <span class="aula-streak-banner__fire">🔥</span>
                <strong>¡Llevas <?= (int)$streak; ?> días seguidos!</strong>
                Sigue así para desbloquear más privilegios del curso.
            </div>
            <?php endif; ?>

            <!-- Cabecera de lección -->
            <div class="aula-lesson-header">
                <p class="aula-lesson-header__module">
                    <i class="bi bi-collection"></i>
                    <?= htmlspecialchars($activeLesson['module_title'], ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <h1 class="aula-lesson-header__title">
                    <?= htmlspecialchars($activeLesson['title'], ENT_QUOTES, 'UTF-8'); ?>
                </h1>
                <?php if ($activeLesson['duration_minutes']): ?>
                    <span class="aula-lesson-header__duration">
                        <i class="bi bi-clock"></i> <?= (int)$activeLesson['duration_minutes']; ?> minutos
                    </span>
                <?php endif; ?>
            </div>

            <!-- Media de la lección (vídeos, imágenes) -->
            <?php if (!empty($lessonMedia)): ?>
                <div class="aula-media-list">
                    <?php foreach ($lessonMedia as $media): ?>
                        <?php if ($media['type'] === 'video'): ?>
                            <div class="aula-media-item aula-media-item--video">
                                <video controls poster="<?= htmlspecialchars($resolveMediaPath($media['thumbnail_url'] ?? '', $videoPosterFallback), ENT_QUOTES, 'UTF-8'); ?>">
                                    <source src="<?= htmlspecialchars('../' . ltrim($media['url'], '/'), ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
                                    Tu navegador no soporta el reproductor de vídeo.
                                </video>
                                <?php if ($media['title']): ?>
                                    <p class="aula-media-item__caption"><?= htmlspecialchars($media['title'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($media['type'] === 'image'): ?>
                            <div class="aula-media-item aula-media-item--image">
                                  <img src="<?= htmlspecialchars('../' . ltrim($media['url'], '/'), ENT_QUOTES, 'UTF-8'); ?>"
                                     alt="<?= htmlspecialchars($media['title'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php if ($media['description']): ?>
                                    <p class="aula-media-item__caption"><?= htmlspecialchars($media['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($media['type'] === 'pdf'): ?>
                            <div class="aula-media-item aula-media-item--pdf">
                                <i class="bi bi-file-earmark-pdf-fill"></i>
                                          <a href="<?= htmlspecialchars('../' . ltrim($media['url'], '/'), ENT_QUOTES, 'UTF-8'); ?>"
                                   target="_blank" rel="noopener noreferrer">
                                    <?= htmlspecialchars($media['title'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </div>
                        <?php elseif ($media['type'] === 'link'): ?>
                            <div class="aula-media-item aula-media-item--link">
                                <i class="bi bi-link-45deg"></i>
                                <a href="<?= htmlspecialchars($media['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                   target="_blank" rel="noopener noreferrer">
                                    <?= htmlspecialchars($media['title'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Cuerpo de la lección -->
            <div class="aula-lesson-body">
                <?= $activeLesson['body'] ?? ''; ?>
            </div>

            <div class="d-flex justify-content-end mt-3 mb-3">
                <form method="POST" action="?slug=<?= urlencode($slug); ?>&lesson=<?= (int) ($activeLesson['id'] ?? 0); ?>">
                    <input type="hidden" name="mark_completed" value="1">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i>Marcar lección como completada
                    </button>
                </form>
            </div>

            <!-- Navegación entre lecciones -->
            <?php
            // Construir lista plana de lecciones para navegar prev/next
            $allLessons = [];
            foreach ($modules as $moduleData) {
                foreach ($moduleData['lessons'] as $lessonData) {
                    $allLessons[] = $lessonData;
                }
            }
            $currentIdx = array_search((int)$activeLesson['id'], array_column($allLessons, 'id'));
            $prevLesson = $currentIdx > 0 ? $allLessons[$currentIdx - 1] : null;
            $nextLesson = isset($allLessons[$currentIdx + 1]) ? $allLessons[$currentIdx + 1] : null;
            ?>
            <div class="aula-lesson-nav">
                <?php if ($prevLesson): ?>
                    <a href="?slug=<?= urlencode($slug); ?>&lesson=<?= (int)$prevLesson['id']; ?>" class="aula-lesson-nav__btn aula-lesson-nav__btn--prev">
                        <i class="bi bi-arrow-left"></i> Lección anterior
                        <span><?= htmlspecialchars($prevLesson['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </a>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>
                <?php if ($nextLesson): ?>
                    <a href="?slug=<?= urlencode($slug); ?>&lesson=<?= (int)$nextLesson['id']; ?>" class="aula-lesson-nav__btn aula-lesson-nav__btn--next">
                        Siguiente lección <i class="bi bi-arrow-right"></i>
                        <span><?= htmlspecialchars($nextLesson['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </a>
                <?php else: ?>
                    <div class="aula-lesson-nav__completed">
                        <i class="bi bi-trophy-fill"></i> ¡Has completado todas las lecciones!
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Sin lección seleccionada: mostrar bienvenida + privilegios -->
            <div class="aula-welcome">
                <div class="aula-welcome__header">
                    <h2><?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p>Selecciona una lección del menú para comenzar.</p>
                </div>

                <!-- Stats destacados -->
                <div class="aula-welcome__stats">
                    <div class="aula-welcome__stat">
                        <span class="aula-welcome__stat-big">🔥 <?= (int)($streak ?? 0); ?></span>
                        <span class="aula-welcome__stat-label">días de racha</span>
                    </div>
                    <div class="aula-welcome__stat">
                        <span class="aula-welcome__stat-big">⏱️ <?= $totalHours > 0 ? $totalHours . 'h ' . $remMinutes . 'm' : $remMinutes . 'm'; ?></span>
                        <span class="aula-welcome__stat-label">tiempo estudiado</span>
                    </div>
                    <div class="aula-welcome__stat">
                        <span class="aula-welcome__stat-big">📈 <?= number_format((float)$completionPct, 0); ?>%</span>
                        <span class="aula-welcome__stat-label">completado</span>
                    </div>
                </div>

                <!-- Privilegios en main content -->
                <h3 class="aula-welcome__section-title"><i class="bi bi-shield-check"></i> Privilegios del curso</h3>
                <div class="aula-privilege-grid">
                    <?php foreach ($privileges ?? [] as $priv): ?>
                        <div class="aula-privilege-card <?= $priv['unlocked'] ? 'aula-privilege-card--unlocked' : 'aula-privilege-card--locked'; ?>">
                            <div class="aula-privilege-card__icon" style="color: <?= $priv['unlocked'] ? htmlspecialchars($priv['color'], ENT_QUOTES, 'UTF-8') : '#aaa'; ?>">
                                <i class="bi <?= htmlspecialchars($priv['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                            </div>
                            <div class="aula-privilege-card__body">
                                <strong><?= htmlspecialchars($priv['label'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <p><?= htmlspecialchars($priv['desc'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <div class="aula-privilege-card__status">
                                <?php if ($priv['unlocked']): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Disponible</span>
                                <?php else: ?>
                                    <span class="aula-privilege-card__lock"><i class="bi bi-lock-fill"></i></span>
                                    <span class="aula-privilege-card__req"><?= htmlspecialchars($priv['requirement'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </main>
</div>

<a href="<?= htmlspecialchars(appUrl('student/chatbot.php?slug=' . urlencode((string) $slug)), ENT_QUOTES, 'UTF-8'); ?>" class="site-chatbot-fab" id="siteChatbotFab" aria-label="Abrir chatbot" title="Abrir chatbot" style="position:fixed;right:20px;bottom:20px;z-index:2147483000;display:inline-flex;">
    <span>Chatbot</span>
</a>

<div class="modal fade" id="chatbotModal" tabindex="-1" aria-labelledby="chatbotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="height:min(86vh, 860px);">
            <div class="modal-header">
                <h5 class="modal-title" id="chatbotModalLabel"><i class="bi bi-robot me-2"></i>Asistente SITECRAFT</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="chatbotModalFrame" title="Chatbot SITECRAFT" src="about:blank" style="width:100%;height:100%;border:0;min-height:520px;"></iframe>
            </div>
        </div>
    </div>
</div>

<script src="<?= htmlspecialchars(appUrl('assets/js/bootstrap.bundle.min.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var chatbotFab = document.getElementById('siteChatbotFab');
    var chatbotModalEl = document.getElementById('chatbotModal');
    var chatbotFrame = document.getElementById('chatbotModalFrame');

    if (!chatbotFab) {
        return;
    }

    chatbotFab.addEventListener('click', function (event) {
        event.preventDefault();

        if (!chatbotModalEl || !chatbotFrame || typeof bootstrap === 'undefined') {
            window.location.href = chatbotFab.getAttribute('href');
            return;
        }

        var targetUrl = chatbotFab.getAttribute('href');
        if (targetUrl && chatbotFrame.getAttribute('src') !== targetUrl) {
            chatbotFrame.setAttribute('src', targetUrl);
        }

        bootstrap.Modal.getOrCreateInstance(chatbotModalEl).show();
    });

    chatbotModalEl.addEventListener('hidden.bs.modal', function () {
        chatbotFrame.setAttribute('src', 'about:blank');
    });
});
</script>
</body>
</html>
