<?php
// Vista conversacional del chatbot del curso activo del alumno.
if (empty($embed)) {
    require __DIR__ . '/../layouts/header.php';
} else {
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/base/bootstrap.min.css'), ENT_QUOTES, 'UTF-8'); ?>">
<link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/icons/bootstrap-icons.css'), ENT_QUOTES, 'UTF-8'); ?>">
<link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/pages/student/chatbot.css'), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body>
<?php } ?>

<div class="container my-4 chatbot-page">
    <div class="chatbot-page__header mb-3">
        <div>
            <h1>Asistente del curso</h1>
            <p class="text-muted mb-0">
                <?= htmlspecialchars($course['title'] ?? 'Curso', ENT_QUOTES, 'UTF-8'); ?>
            </p>
        </div>
        <?php if (!empty($course['slug']) && empty($embed)): ?>
            <a href="<?= htmlspecialchars(appUrl('student/curso.php?slug=' . urlencode((string) $course['slug'])), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Volver al aula
            </a>
        <?php endif; ?>
    </div>

    <?php if ($errorMessage !== null): ?>
        <div class="alert alert-warning" role="alert">
            <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php else: ?>
        <div class="chat-window">
            <div class="chat-window__messages" id="chatMessages">
                <?php if (empty($messages)): ?>
                    <div class="chat-msg chat-msg--assistant">
                        <div class="chat-msg__bubble">
                            <?= htmlspecialchars((string) ($chatConfig['welcome_prompt'] ?? 'Hola. Soy tu asistente del curso. Pregúntame cualquier duda.'), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <?php $isUser = ($msg['message_role'] ?? '') === 'user'; ?>
                        <div class="chat-msg <?= $isUser ? 'chat-msg--user' : 'chat-msg--assistant'; ?>">
                            <div class="chat-msg__bubble">
                                <?= nl2br(htmlspecialchars((string) ($msg['content'] ?? ''), ENT_QUOTES, 'UTF-8')); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form class="chat-window__form" id="chatForm" method="POST" action="<?= htmlspecialchars(appUrl('student/chatbot.php?slug=' . urlencode((string) ($course['slug'] ?? '')) . (!empty($embed) ? '&embed=1' : '')), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="chat-suggestions" id="chatSuggestions">
                    <button type="button" class="chat-suggestions__toggle" id="suggestionsToggle" aria-expanded="true" aria-controls="suggestionsBody">
                        <i class="bi bi-lightbulb me-1"></i>Preguntas frecuentes
                        <i class="bi bi-chevron-up ms-auto chat-suggestions__chevron" id="suggestionsChevron"></i>
                    </button>
                    <div class="chat-suggestions__chips" id="suggestionsBody">
                        <button type="button" class="chat-suggestion-chip" data-q="¿Qué es la biomecánica del movimiento?">¿Qué es la biomecánica?</button>
                        <button type="button" class="chat-suggestion-chip" data-q="¿Cómo se hace una evaluación física inicial?">Evaluación física inicial</button>
                        <button type="button" class="chat-suggestion-chip" data-q="¿Cómo planificar una sesión de entrenamiento?">Planificar una sesión</button>
                        <button type="button" class="chat-suggestion-chip" data-q="¿Qué es la prescripción del ejercicio?">Prescripción del ejercicio</button>
                        <button type="button" class="chat-suggestion-chip" data-q="¿Cómo mejorar la movilidad articular?">Movilidad articular</button>
                        <button type="button" class="chat-suggestion-chip" data-q="¿Qué es el VO2 máximo?">VO2 máximo</button>
                        <button type="button" class="chat-suggestion-chip" data-q="¿Cuáles son los principios del entrenamiento?">Principios del entrenamiento</button>
                        <button type="button" class="chat-suggestion-chip" data-q="¿Qué es la frecuencia cardíaca máxima?">Frecuencia cardíaca</button>
                    </div>
                </div>
                <textarea name="message" id="chatTextarea" class="form-control" rows="2" placeholder="Escribe tu pregunta aquí..." required></textarea>
                <button type="submit" class="btn btn-dark mt-2 w-100">
                    <i class="bi bi-send me-1"></i>Enviar pregunta
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    // Mantiene el scroll en el último mensaje al cargar el historial.
    var box = document.getElementById('chatMessages');
    if (box) {
        box.scrollTop = box.scrollHeight;
    }

    // Toggle para mostrar/ocultar los chips de sugerencias.
    var toggle      = document.getElementById('suggestionsToggle');
    var body        = document.getElementById('suggestionsBody');
    var chevron     = document.getElementById('suggestionsChevron');
    var isOpen      = true;

    if (toggle && body) {
        toggle.addEventListener('click', function () {
            isOpen = !isOpen;
            body.style.display   = isOpen ? '' : 'none';
            toggle.setAttribute('aria-expanded', String(isOpen));
            if (chevron) {
                chevron.className = isOpen
                    ? 'bi bi-chevron-up ms-auto chat-suggestions__chevron'
                    : 'bi bi-chevron-down ms-auto chat-suggestions__chevron';
            }
        });
    }

    // Chips de sugerencia: al clicar rellena el textarea y envía el formulario.
    var chips    = document.querySelectorAll('.chat-suggestion-chip');
    var form     = document.getElementById('chatForm');
    var textarea = document.getElementById('chatTextarea');

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            if (!form || !textarea) return;
            textarea.value = chip.getAttribute('data-q') || chip.textContent.trim();
            textarea.removeAttribute('required');
            form.submit();
        });
    });
})();
</script>

<?php
if (empty($embed)) {
    require __DIR__ . '/../layouts/footer.php';
} else {
?>
<script src="<?= htmlspecialchars(appUrl('assets/js/bootstrap.bundle.min.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
<?php } ?>
