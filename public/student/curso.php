<?php

declare(strict_types=1);

require __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/index.php';

// Recupera un curso publicado a partir de su slug público.
function findPublishedCourseBySlug(PDO $pdo, string $slug): array|false
{
    $statement = $pdo->prepare('SELECT * FROM courses WHERE slug = ? AND is_published = 1 LIMIT 1');
    $statement->execute([$slug]);

    return $statement->fetch();
}

// Busca la matrícula del usuario para el curso actual.
function findEnrollmentForCourse(PDO $pdo, int $userId, int $courseId): array|false
{
    $statement = $pdo->prepare(<<<'SQL'
        SELECT * FROM enrollments
        WHERE user_id = ? AND course_id = ?
        LIMIT 1
    SQL);
    $statement->execute([$userId, $courseId]);

    return $statement->fetch();
}

// Agrupa filas de módulos y lecciones en una estructura lista para la vista.
function mapModulesWithLessons(array $rows): array
{
    $modules = [];

    foreach ($rows as $row) {
        $moduleId = $row['id'];

        if (!isset($modules[$moduleId])) {
            $modules[$moduleId] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'sort_order' => $row['sort_order'],
                'lessons' => [],
            ];
        }

        if ($row['lesson_id'] !== null) {
            $modules[$moduleId]['lessons'][] = [
                'id' => $row['lesson_id'],
                'title' => $row['lesson_title'],
                'duration_minutes' => $row['duration_minutes'],
                'sort_order' => $row['lesson_sort_order'],
                'is_free_preview' => $row['is_free_preview'],
            ];
        }
    }

    return array_values($modules);
}

requireAuth();

$user = getCurrentUser();

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    header('Location: ' . appUrl('student/dashboard.php'));
    exit;
}

// --- Cargar curso ---
$course = null;
try {
    $course = findPublishedCourseBySlug(db(), $slug) ?: null;
} catch (Throwable) {}

if (!$course) {
    header('Location: ' . appUrl('student/dashboard.php'));
    exit;
}

// --- Verificar matrícula activa ---
$enrollment = null;
try {
    $enrollment = findEnrollmentForCourse(db(), (int) $user['id'], (int) $course['id']) ?: null;
} catch (Throwable) {}

if (!$enrollment || !in_array($enrollment['enrollment_status'], ['active', 'completed'], true)) {
    header('Location: ' . appUrl('curso.php?slug=' . urlencode($slug)));
    exit;
}

// --- Módulos y lecciones ---
$modules = [];
try {
    $stmt = db()->prepare(<<<'SQL'
        SELECT m.id, m.title, m.description, m.sort_order,
               l.id AS lesson_id,
               l.title AS lesson_title,
               l.duration_minutes,
               l.sort_order AS lesson_sort_order,
               l.is_free_preview
        FROM course_modules m
        LEFT JOIN course_lessons l ON l.module_id = m.id AND l.is_visible = 1
        WHERE m.course_id = ? AND m.is_visible = 1
        ORDER BY m.sort_order ASC, l.sort_order ASC
    SQL);
    $stmt->execute([$course['id']]);
    $modules = mapModulesWithLessons($stmt->fetchAll());
} catch (Throwable) {}

// --- Lección activa ---
$lessonId = (int) ($_GET['lesson'] ?? 0);

// Si no se indica lección, usar la primera
if ($lessonId === 0 && !empty($modules)) {
    foreach ($modules as $moduleItem) {
        if (!empty($moduleItem['lessons'])) {
            $lessonId = (int) $moduleItem['lessons'][0]['id'];
            break;
        }
    }
}

$activeLesson = null;
$lessonMedia  = [];
$resourceCountByLesson = [];

if ($lessonId > 0) {
    try {
        $stmt = db()->prepare(<<<'SQL'
            SELECT l.*, m.title AS module_title
            FROM course_lessons l
            JOIN course_modules m ON l.module_id = m.id
            WHERE l.id = ? AND m.course_id = ?
            LIMIT 1
        SQL);
        $stmt->execute([$lessonId, $course['id']]);
        $activeLesson = $stmt->fetch();
    } catch (Throwable) {}

    if ($activeLesson) {
        try {
            $stmt = db()->prepare(<<<'SQL'
                SELECT * FROM course_media
                WHERE lesson_id = ? AND is_visible = 1
                ORDER BY sort_order ASC
            SQL);
            $stmt->execute([$lessonId]);
            $lessonMedia = $stmt->fetchAll();
        } catch (Throwable) {}
    }
}

try {
    $stmt = db()->prepare(<<<'SQL'
        SELECT cm.lesson_id, COUNT(*) AS total_resources
        FROM course_media cm
        JOIN course_lessons l ON l.id = cm.lesson_id
        JOIN course_modules m ON m.id = l.module_id
        WHERE m.course_id = ?
          AND cm.lesson_id IS NOT NULL
          AND cm.is_visible = 1
          AND l.is_visible = 1
          AND m.is_visible = 1
        GROUP BY cm.lesson_id
    SQL);
    $stmt->execute([$course['id']]);
    foreach ($stmt->fetchAll() as $row) {
        $resourceCountByLesson[(int) $row['lesson_id']] = (int) $row['total_resources'];
    }
} catch (Throwable) {
    $resourceCountByLesson = [];
}

// --- Infraestructura de tracking por lección ---
try {
    db()->exec(<<<'SQL'
        CREATE TABLE IF NOT EXISTS enrollment_lesson_progress (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            enrollment_id BIGINT UNSIGNED NOT NULL,
            lesson_id BIGINT UNSIGNED NOT NULL,
            lesson_viewed TINYINT(1) NOT NULL DEFAULT 0,
            resources_reviewed TINYINT(1) NOT NULL DEFAULT 0,
            is_completed TINYINT(1) NOT NULL DEFAULT 0,
            first_viewed_at DATETIME NULL,
            last_viewed_at DATETIME NULL,
            completed_at DATETIME NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_enrollment_lesson_progress (enrollment_id, lesson_id),
            CONSTRAINT fk_elp_enrollment FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_elp_lesson FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE ON UPDATE CASCADE,
            KEY idx_elp_lesson (lesson_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL);
} catch (Throwable) {
    // Si el entorno no permite crear tabla, la vista seguirá cargando sin métricas avanzadas.
}

// --- Registrar actividad de la lección activa ---
if ($activeLesson) {
    $activeLessonId = (int) $activeLesson['id'];
    $hasMedia = (int) ($resourceCountByLesson[$activeLessonId] ?? 0) > 0;
    $resourcesReviewed = $hasMedia ? 1 : 0;
    $markCompleted = $_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['mark_completed'] ?? '') === '1');

    try {
        $stmt = db()->prepare(<<<'SQL'
            INSERT INTO enrollment_lesson_progress (
                enrollment_id,
                lesson_id,
                lesson_viewed,
                resources_reviewed,
                is_completed,
                first_viewed_at,
                last_viewed_at,
                completed_at
            ) VALUES (?, ?, 1, ?, ?, NOW(), NOW(), ?)
            ON DUPLICATE KEY UPDATE
                lesson_viewed = 1,
                resources_reviewed = GREATEST(resources_reviewed, VALUES(resources_reviewed)),
                is_completed = GREATEST(is_completed, VALUES(is_completed)),
                last_viewed_at = NOW(),
                completed_at = IF(VALUES(is_completed) = 1 AND completed_at IS NULL, NOW(), completed_at)
        SQL);

        $stmt->execute([
            (int) $enrollment['id'],
            $activeLessonId,
            $resourcesReviewed,
            $markCompleted ? 1 : 0,
            $markCompleted ? date('Y-m-d H:i:s') : null,
        ]);
    } catch (Throwable) {
        // Continuar aunque no se pueda registrar la actividad.
    }

    // Sumar minutos solo una vez por lección y día para evitar inflar el tracking por refrescos.
    $today = date('Y-m-d');
    $dailySessionKey = 'tracked_lessons_' . $today;
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION[$dailySessionKey]) || !is_array($_SESSION[$dailySessionKey])) {
        $_SESSION[$dailySessionKey] = [];
    }

    if (!in_array($activeLessonId, $_SESSION[$dailySessionKey], true)) {
        $estimatedMinutes = max(3, min(30, (int) ($activeLesson['duration_minutes'] ?? 8)));

        try {
            $stmt = db()->prepare('SELECT id, minutes_spent FROM study_logs WHERE enrollment_id = ? AND studied_on = ? LIMIT 1');
            $stmt->execute([(int) $enrollment['id'], $today]);
            $dailyLog = $stmt->fetch();

            if ($dailyLog) {
                $upd = db()->prepare('UPDATE study_logs SET minutes_spent = ? WHERE id = ?');
                $upd->execute([(int) $dailyLog['minutes_spent'] + $estimatedMinutes, (int) $dailyLog['id']]);
            } else {
                $ins = db()->prepare('INSERT INTO study_logs (enrollment_id, studied_on, minutes_spent) VALUES (?, ?, ?)');
                $ins->execute([(int) $enrollment['id'], $today, $estimatedMinutes]);
            }
        } catch (Throwable) {
            // Mantener navegación aunque falle el tracking de minutos.
        }

        $_SESSION[$dailySessionKey][] = $activeLessonId;
    }
}

// --- Métricas de seguimiento ---
$progress = [
    'completion_percent' => 0.0,
];
$streak = 0;
$totalMinutes = 0;
$privileges = [];

try {
    $stmt = db()->prepare('SELECT COALESCE(SUM(minutes_spent), 0) AS total_minutes FROM study_logs WHERE enrollment_id = ?');
    $stmt->execute([(int) $enrollment['id']]);
    $totalMinutes = (int) ($stmt->fetchColumn() ?: 0);
} catch (Throwable) {
    $totalMinutes = 0;
}

try {
    $stmt = db()->prepare('SELECT DISTINCT studied_on FROM study_logs WHERE enrollment_id = ? ORDER BY studied_on DESC');
    $stmt->execute([(int) $enrollment['id']]);
    $studyDays = array_map(static fn($d): string => (string) $d, $stmt->fetchAll(PDO::FETCH_COLUMN));

    if ($studyDays !== []) {
        $expected = new DateTimeImmutable($studyDays[0]);
        foreach ($studyDays as $day) {
            $current = new DateTimeImmutable($day);
            if ($current->format('Y-m-d') !== $expected->format('Y-m-d')) {
                break;
            }

            $streak++;
            $expected = $expected->modify('-1 day');
        }
    }
} catch (Throwable) {
    $streak = 0;
}

$lessonProgressMap = [];
try {
    $stmt = db()->prepare(<<<'SQL'
        SELECT lp.lesson_id, lp.lesson_viewed, lp.resources_reviewed, lp.is_completed
        FROM enrollment_lesson_progress lp
        JOIN course_lessons l ON l.id = lp.lesson_id
        JOIN course_modules m ON m.id = l.module_id
        WHERE lp.enrollment_id = ?
          AND m.course_id = ?
          AND l.is_visible = 1
          AND m.is_visible = 1
    SQL);
    $stmt->execute([(int) $enrollment['id'], (int) $course['id']]);

    foreach ($stmt->fetchAll() as $row) {
        $lessonProgressMap[(int) $row['lesson_id']] = [
            'viewed' => (int) ($row['lesson_viewed'] ?? 0) === 1,
            'resources_reviewed' => (int) ($row['resources_reviewed'] ?? 0) === 1,
            'completed' => (int) ($row['is_completed'] ?? 0) === 1,
        ];
    }
} catch (Throwable) {
    $lessonProgressMap = [];
}

$totalLessons = 0;
$progressScore = 0.0;

foreach ($modules as $moduleItem) {
    foreach ($moduleItem['lessons'] as $lesson) {
        $totalLessons++;
        $lessonIdIter = (int) $lesson['id'];
        $entry = $lessonProgressMap[$lessonIdIter] ?? ['viewed' => false, 'resources_reviewed' => false, 'completed' => false];
        $hasMedia = (int) ($resourceCountByLesson[$lessonIdIter] ?? 0) > 0;

        if ($entry['viewed']) {
            $progressScore += 0.6;
        }

        if (!$hasMedia || $entry['resources_reviewed']) {
            $progressScore += 0.2;
        }

        if ($entry['completed']) {
            $progressScore += 0.2;
        }
    }
}

$completionPctVal = $totalLessons > 0
    ? max(0.0, min(100.0, ($progressScore / $totalLessons) * 100.0))
    : 0.0;

$progress['completion_percent'] = $completionPctVal;

$privileges = [
    [
        'label' => 'Foro privado del curso',
        'desc' => 'Acceso al espacio privado de alumnos activos.',
        'requirement' => 'Completa al menos el 20% del curso',
        'icon' => 'bi-people-fill',
        'color' => '#6f42c1',
        'unlocked' => $completionPctVal >= 20,
    ],
    [
        'label' => 'Pack de recursos premium',
        'desc' => 'Material adicional y guías descargables.',
        'requirement' => '45% completado y 60 minutos acumulados',
        'icon' => 'bi-folder2-open',
        'color' => '#198754',
        'unlocked' => $completionPctVal >= 45 && $totalMinutes >= 60,
    ],
    [
        'label' => 'Sesión grupal con tutor',
        'desc' => 'Invitación a una sesión de seguimiento en directo.',
        'requirement' => '70% completado y racha de 3 días',
        'icon' => 'bi-camera-video-fill',
        'color' => '#fd7e14',
        'unlocked' => $completionPctVal >= 70 && $streak >= 3,
    ],
    [
        'label' => 'Certificado final habilitado',
        'desc' => 'Ya puedes solicitar tu certificado de finalización.',
        'requirement' => 'Completa el 100% del curso',
        'icon' => 'bi-patch-check-fill',
        'color' => '#0d6efd',
        'unlocked' => $completionPctVal >= 100,
    ],
];

try {
    $stmt = db()->prepare(<<<'SQL'
        INSERT INTO progress_summary (enrollment_id, total_minutes, consecutive_days, completion_percent)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            total_minutes = VALUES(total_minutes),
            consecutive_days = VALUES(consecutive_days),
            completion_percent = VALUES(completion_percent)
    SQL);
    $stmt->execute([
        (int) $enrollment['id'],
        $totalMinutes,
        $streak,
        round($completionPctVal, 2),
    ]);
} catch (Throwable) {
    // Si falla el guardado, mantener métricas calculadas en memoria para la vista.
}

// --- Profesor ---
$teacher = null;
try {
    $stmt = db()->prepare(<<<'SQL'
        SELECT u.full_name FROM users u
        JOIN course_teachers ct ON ct.user_id = u.id
        WHERE ct.course_id = ? LIMIT 1
    SQL);
    $stmt->execute([$course['id']]);
    $teacher = $stmt->fetchColumn();
} catch (Throwable) {}

// Total de lecciones para progreso
$totalLessons = 0;
foreach ($modules as $moduleItem) {
    $totalLessons += count($moduleItem['lessons']);
}

$pageTitle   = 'SITECRAFT | ' . htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8');
$currentPage = 'student-curso';
$pageCss     = ['assets/css/pages/student/curso.css'];

require __DIR__ . '/../../app/views/student/curso.php';
