-- ============================================================
-- FIX: Corregir roles de profesores
-- El seed inicial asignaba role_id=3 (student) a los profesores
-- por error. Este script añade el rol teacher correcto.
-- NOTA: No se elimina el rol student — un usuario puede tener
--       ambos roles a la vez (teacher + student).
-- Ejecutar en phpMyAdmin o MySQL CLI
-- ============================================================

USE sitecraft;

-- Añadir rol teacher a los profesores de prueba
-- (INSERT IGNORE no falla si ya existe la combinación)
INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
JOIN roles r ON r.code = 'teacher'
WHERE u.email IN (
    'laura.martin@sitecraft.local',
    'james.walker@sitecraft.local',
    'claudia.perez@sitecraft.local',
    'adrian.gomez@sitecraft.local'
);

-- Verificar resultado: muestra todos los roles de cada usuario
SELECT u.email, GROUP_CONCAT(r.code ORDER BY r.code SEPARATOR ', ') AS roles
FROM users u
JOIN user_roles ur ON u.id = ur.user_id
JOIN roles r ON r.id = ur.role_id
WHERE u.email IN (
    'laura.martin@sitecraft.local',
    'james.walker@sitecraft.local',
    'claudia.perez@sitecraft.local',
    'adrian.gomez@sitecraft.local',
    'admin@sitecraft.local'
)
GROUP BY u.id, u.email
ORDER BY u.email;
