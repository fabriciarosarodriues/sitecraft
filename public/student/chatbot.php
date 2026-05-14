<?php

declare(strict_types=1);

require __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/index.php';

// Controlador del chatbot del alumno con validaciones de acceso por curso.
requireAuth();
$user = getCurrentUser();

$slug = trim((string) ($_GET['slug'] ?? ''));
$embed = !empty($_GET['embed']);
if ($slug === '') {
    header('Location: ' . appUrl('student/dashboard.php'));
    exit;
}

$course = null;
$enrollment = null;
$chatConfig = null;
$messages = [];
$errorMessage = null;

try {
    $stmt = db()->prepare('SELECT * FROM courses WHERE slug = ? AND is_published = 1 LIMIT 1');
    $stmt->execute([$slug]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($course === null) {
        throw new RuntimeException('Curso no encontrado.');
    }

    $stmt = db()->prepare('SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? LIMIT 1');
    $stmt->execute([(int) $user['id'], (int) $course['id']]);
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($enrollment === null || !in_array((string) $enrollment['enrollment_status'], ['active', 'completed'], true)) {
        throw new RuntimeException('No tienes acceso al chatbot de este curso.');
    }

    $stmt = db()->prepare('SELECT * FROM chatbot_configs WHERE course_id = ? LIMIT 1');
    $stmt->execute([(int) $course['id']]);
    $chatConfig = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($chatConfig === null || (int) ($chatConfig['is_enabled'] ?? 0) !== 1) {
        throw new RuntimeException('El chatbot de este curso no está disponible en este momento.');
    }
} catch (Throwable $exception) {
    $errorMessage = $exception->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errorMessage === null && $course !== null) {
    // Guarda pregunta del alumno y respuesta del asistente en historial.
    $question = trim((string) ($_POST['message'] ?? ''));

    if ($question !== '') {
        try {
            $insertUser = db()->prepare(<<<'SQL'
                INSERT INTO chat_messages (user_id, course_id, enrollment_id, message_role, content)
                VALUES (?, ?, ?, 'user', ?)
            SQL);
            $insertUser->execute([
                (int) $user['id'],
                (int) $course['id'],
                (int) $enrollment['id'],
                $question,
            ]);

            $answer = generateTrainerAssistantAnswer(db(), (int) $course['id'], $question, (string) ($chatConfig['welcome_prompt'] ?? ''));

            $insertAssistant = db()->prepare(<<<'SQL'
                INSERT INTO chat_messages (user_id, course_id, enrollment_id, message_role, content)
                VALUES (?, ?, ?, 'assistant', ?)
            SQL);
            $insertAssistant->execute([
                (int) $user['id'],
                (int) $course['id'],
                (int) $enrollment['id'],
                $answer,
            ]);

            $redirectUrl = appUrl('student/chatbot.php?slug=' . urlencode($slug) . ($embed ? '&embed=1' : ''));
            header('Location: ' . $redirectUrl);
            exit;
        } catch (Throwable) {
            $errorMessage = 'No se pudo enviar el mensaje. Inténtalo de nuevo.';
        }
    }
}

const CHATBOT_NOT_UNDERSTOOD = 'No te estoy entendiendo. Selecciona alguna de estas preguntas:';

$showSuggestions = false;

if ($errorMessage === null && $course !== null) {
    // Recupera últimos mensajes para reconstruir el contexto en pantalla.
    try {
        $stmt = db()->prepare(<<<'SQL'
            SELECT message_role, content, created_at
            FROM chat_messages
            WHERE user_id = ? AND course_id = ?
            ORDER BY id DESC
            LIMIT 30
        SQL);
        $stmt->execute([(int) $user['id'], (int) $course['id']]);
        $messages = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Throwable) {
        $messages = [];
    }

    // Muestra chips si no hay historial o si el último mensaje no fue entendido.
    $lastMsg = end($messages);
    $showSuggestions = $messages === []
        || (is_array($lastMsg)
            && ($lastMsg['message_role'] ?? '') === 'assistant'
            && ($lastMsg['content'] ?? '') === CHATBOT_NOT_UNDERSTOOD);
}

function generateTrainerAssistantAnswer(PDO $pdo, int $courseId, string $question, string $fallbackWelcome): string
{
    // Motor local simple: busca lecciones relevantes por palabras clave.
    $normalized = mb_strtolower(trim($question));

    if ($normalized === '' || preg_match('/^(hola|buenas|hey|hello)$/u', $normalized) === 1) {
        return $fallbackWelcome !== ''
            ? $fallbackWelcome
            : 'Hola. Soy tu asistente del curso de Entrenador Personal Nivel I. Dime qué tema quieres repasar.';
    }

    $tokens = preg_split('/[^\p{L}\p{N}]+/u', $normalized) ?: [];
    $tokens = array_values(array_filter($tokens, static function (string $token): bool {
        return mb_strlen($token) >= 4;
    }));

    $tokens = array_values(array_unique($tokens));
    if (count($tokens) > 6) {
        $tokens = array_slice($tokens, 0, 6);
    }

    $where = [];
    $params = [':course_id' => $courseId];

    foreach ($tokens as $tokenIndex => $token) {
        $keyTitle = ':kt' . $tokenIndex;
        $keyBody  = ':kb' . $tokenIndex;
        $where[] = '(LOWER(l.title) LIKE ' . $keyTitle . ' OR LOWER(l.body) LIKE ' . $keyBody . ')';
        $params[$keyTitle] = '%' . $token . '%';
        $params[$keyBody]  = '%' . $token . '%';
    }

    $sql = <<<SQL
        SELECT l.title, l.body, m.title AS module_title
        FROM course_lessons l
        JOIN course_modules m ON m.id = l.module_id
        WHERE m.course_id = :course_id
          AND l.is_visible = 1
          AND m.is_visible = 1
    SQL;

    if ($where !== []) {
        $sql .= ' AND (' . implode(' OR ', $where) . ')';
    }

    $sql .= ' ORDER BY m.sort_order ASC, l.sort_order ASC LIMIT 3';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($lessons === []) {
        return CHATBOT_NOT_UNDERSTOOD;
    }

    $lines = [];
    $lines[] = 'Te ayudo con eso. Estas son las partes del curso más relacionadas:';

    foreach ($lessons as $lesson) {
        $summary = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($lesson['body'] ?? ''))));
        $summary = mb_substr($summary, 0, 220);
        if ($summary !== '') {
            $summary .= '...';
        }

        $lines[] = '- ' . (string) $lesson['module_title'] . ' > ' . (string) $lesson['title'];
        if ($summary !== '') {
            $lines[] = '  ' . $summary;
        }
    }

    $lines[] = 'Si quieres, te explico uno de estos temas paso a paso con ejemplos prácticos de sala.';

    return implode("\n", $lines);
}

$pageTitle = 'SITECRAFT | Chatbot del curso';
$currentPage = 'student-chatbot';
$pageCss = ['assets/css/pages/student/chatbot.css'];

require __DIR__ . '/../../app/views/student/chatbot.php';
