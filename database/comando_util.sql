-- Comando SQL para asignar rol de estudiante y matricular

USE sitecraft;

-- Asignar rol student (si no lo tiene)
INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
JOIN roles r ON r.code = 'student'
WHERE u.email = 'fabricia@sitecraft.local';

-- Matricular en el curso como activa
INSERT INTO enrollments (user_id, course_id, enrollment_status, started_at, ends_at)
SELECT 
    u.id,
    c.id,
    'active',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 60 DAY)
FROM users u, courses c
WHERE u.email = 'fabricia@sitecraft.local'
  AND c.slug = 'entrenador-personal-nivel-i'
ON DUPLICATE KEY UPDATE
    enrollment_status = 'active',
    started_at = NOW(),
    ends_at = DATE_ADD(NOW(), INTERVAL 60 DAY);