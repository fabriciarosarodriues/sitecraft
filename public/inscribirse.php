<?php

declare(strict_types=1);

require __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/index.php';

// Verificar si el usuario está autenticado
if (!isAuthenticated()) {
    header('Location: ' . appUrl('login.php'));
    exit;
}

$user = getCurrentUser();
$courseId = (int) ($_POST['course_id'] ?? 0);
$courseSlug = trim($_POST['course_slug'] ?? '');
$paymentMethod = trim($_POST['payment_method'] ?? '');
$acceptTerms = (bool) ($_POST['accept_terms'] ?? false);

// Validaciones básicas de entrada para no procesar formularios incompletos.
if ($courseId === 0 || $courseSlug === '' || !$acceptTerms) {
    header('Location: ' . appUrl('curso.php?slug=' . urlencode($courseSlug) . '&enroll=error&message=datos_incompletos'));
    exit;
}

try {
    $pdo = db();

    // Verifica que el curso exista y esté publicado antes de matricular.
    $stmtCourse = $pdo->prepare('SELECT id, price, currency FROM courses WHERE id = ? AND is_published = 1 LIMIT 1');
    $stmtCourse->execute([$courseId]);
    $course = $stmtCourse->fetch();

    if (!$course) {
        header('Location: ' . appUrl('curso.php?slug=' . urlencode($courseSlug) . '&enroll=error&message=curso_no_encontrado'));
        exit;
    }

    // Evita inscripciones duplicadas por usuario y curso.
    $stmtCheck = $pdo->prepare(<<<'SQL'
        SELECT id FROM enrollments
        WHERE user_id = ? AND course_id = ?
        LIMIT 1
    SQL);
    $stmtCheck->execute([$user['id'], $courseId]);
    $existingEnrollment = $stmtCheck->fetch();

    if ($existingEnrollment) {
        header('Location: ' . appUrl('curso.php?slug=' . urlencode($courseSlug) . '&enroll=error&message=ya_inscrito'));
        exit;
    }

    // Define el estado inicial según si el curso es gratuito o de pago.
    $coursePrice = (float) $course['price'];
    $enrollmentStatus = $coursePrice > 0 ? 'pending_payment' : 'active';

    // Si hay pago, exige método para registrar correctamente la deuda.
    if ($coursePrice > 0 && $paymentMethod === '') {
        header('Location: ' . appUrl('curso.php?slug=' . urlencode($courseSlug) . '&enroll=error&message=metodo_pago_requerido'));
        exit;
    }

    // Crea la matrícula base del alumno en el curso.
    $stmtEnroll = $pdo->prepare(<<<'SQL'
        INSERT INTO enrollments (user_id, course_id, enrollment_status, started_at)
        VALUES (?, ?, ?, NOW())
    SQL);
    $stmtEnroll->execute([$user['id'], $courseId, $enrollmentStatus]);
    $enrollmentId = (int) $pdo->lastInsertId();

    // Si es de pago, crea registro de pago pendiente con vencimiento.
    if ($coursePrice > 0) {
        $stmtPayment = $pdo->prepare(<<<'SQL'
            INSERT INTO payments (enrollment_id, amount, currency, method, payment_status, due_at)
            VALUES (?, ?, ?, ?, 'pending', DATE_ADD(NOW(), INTERVAL 3 DAY))
        SQL);
        $stmtPayment->execute([$enrollmentId, $coursePrice, $course['currency'], $paymentMethod]);

        // Redirige al detalle del curso para completar pago.
        header('Location: ' . appUrl('curso.php?slug=' . urlencode($courseSlug) . '&enroll=pending_payment&enrollment_id=' . $enrollmentId));
    } else {
        // Curso gratuito: acceso inmediato al panel del alumno.
        header('Location: ' . appUrl('student/dashboard.php?enroll=success'));
    }
    exit;

} catch (Throwable $e) {
    error_log('Error en inscripción: ' . $e->getMessage());
    header('Location: ' . appUrl('curso.php?slug=' . urlencode($courseSlug) . '&enroll=error&message=error_sistema'));
    exit;
}
