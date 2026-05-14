</main>
<?php
// Calcula el destino del chatbot según la sesión y los cursos activos del usuario.
$chatbotHref = appUrl('login.php');

if (isset($user) && is_array($user) && isset($user['id'])) {
    $chatbotHref = appUrl('ayuda.php');

    try {
        $stmt = db()->prepare(<<<'SQL'
            SELECT c.slug
            FROM enrollments e
            INNER JOIN courses c ON c.id = e.course_id
            WHERE e.user_id = ?
              AND e.enrollment_status IN ('active', 'completed')
              AND c.is_published = 1
            ORDER BY
              CASE e.enrollment_status
                WHEN 'active' THEN 0
                WHEN 'completed' THEN 1
                ELSE 2
              END,
              e.started_at DESC,
              e.id DESC
            LIMIT 1
        SQL);
        $stmt->execute([(int) $user['id']]);
        $chatbotCourseSlug = $stmt->fetchColumn();

        if (is_string($chatbotCourseSlug) && $chatbotCourseSlug !== '') {
            $chatbotHref = appUrl('student/chatbot.php?slug=' . urlencode($chatbotCourseSlug) . '&embed=1');
        }
    } catch (Throwable) {
        $chatbotHref = appUrl('ayuda.php');
    }
}
?>

<a href="<?= htmlspecialchars($chatbotHref, ENT_QUOTES, 'UTF-8'); ?>" class="site-chatbot-fab" id="siteChatbotFab" aria-label="Abrir chatbot" title="Abrir chatbot" style="position:fixed;right:20px;bottom:20px;z-index:2147483000;display:inline-flex;">
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

<footer class="site-footer" id="contacto">
    <div class="container footer__inner">
        <div>
            <strong>SITECRAFT</strong>
            <p>Plataforma para conectar profesionales con alumnos que quieren aprender y avanzar en sus cursos.</p>
        </div>
        <div>
            <span>Contacto</span>
            <p>
                <a href="<?= htmlspecialchars(appUrl('ayuda.php'), ENT_QUOTES, 'UTF-8'); ?>">Formulario de ayuda</a><br>
                <a href="mailto:soporte@sitecraft.local">soporte@sitecraft.local</a> ·
                <a href="tel:+34900100957">900 100 957</a>
            </p>
        </div>
    </div>
</footer>
<script src="<?= htmlspecialchars(appUrl('assets/js/bootstrap.bundle.min.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?= htmlspecialchars(appUrl('assets/js/app.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Controla la apertura del chatbot dentro del modal en la misma página.
    var chatbotFab = document.getElementById('siteChatbotFab');
    var chatbotModalEl = document.getElementById('chatbotModal');
    var chatbotFrame = document.getElementById('chatbotModalFrame');

    if (chatbotFab) {
        chatbotFab.style.display = 'inline-flex';
        chatbotFab.style.visibility = 'visible';
        chatbotFab.style.opacity = '1';

        chatbotFab.addEventListener('click', function (event) {
            event.preventDefault();

            if (!chatbotModalEl || !chatbotFrame || typeof bootstrap === 'undefined') {
                window.location.href = chatbotFab.getAttribute('href') || '<?= htmlspecialchars(appUrl('login.php'), ENT_QUOTES, 'UTF-8'); ?>';
                return;
            }

            var targetUrl = chatbotFab.getAttribute('href');
            if (targetUrl && chatbotFrame.getAttribute('src') !== targetUrl) {
                chatbotFrame.setAttribute('src', targetUrl);
            }

            bootstrap.Modal.getOrCreateInstance(chatbotModalEl).show();
        });
    }

    if (chatbotModalEl && chatbotFrame) {
        chatbotModalEl.addEventListener('hidden.bs.modal', function () {
            chatbotFrame.setAttribute('src', 'about:blank');
        });
    }
});
</script>
</body>
</html>
