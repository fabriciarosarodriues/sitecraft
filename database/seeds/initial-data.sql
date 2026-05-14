-- SITECRAFT - Datos de ejemplo
-- Inserts iniciales para pruebas de desarrollo

-- Insertar profesores (usuarios)
INSERT INTO users (full_name, email, password_hash, phone, is_active) VALUES
('Laura Martín', 'laura.martin@sitecraft.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '600111222', 1),
('James Walker', 'james.walker@sitecraft.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '600222333', 1),
('Claudia Pérez', 'claudia.perez@sitecraft.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '600333444', 1),
('Adrián Gómez', 'adrian.gomez@sitecraft.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '600444555', 1),
('Admin SITECRAFT', 'admin@sitecraft.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '900100957', 1);

-- Asignar roles a profesores (teacher) y admin usando code en vez de ID hardcodeado
INSERT INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
JOIN roles r ON r.code = 'teacher'
WHERE u.email IN (
    'laura.martin@sitecraft.local',
    'james.walker@sitecraft.local',
    'claudia.perez@sitecraft.local',
    'adrian.gomez@sitecraft.local'
)
ON DUPLICATE KEY UPDATE role_id = VALUES(role_id);

INSERT INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
JOIN roles r ON r.code = 'admin'
WHERE u.email = 'admin@sitecraft.local'
ON DUPLICATE KEY UPDATE role_id = VALUES(role_id);

-- Insertar cursos publicados
INSERT INTO courses (slug, title, description, price, currency, duration_days, is_published) VALUES
(
    'entrenamiento-personal',
    'Entrenamiento Personal',
    'Plan de entrenamiento personalizado con seguimiento semanal. Acceso a área nutricional con análisis de comidas mediante chatbot especializado. Racha diaria, insignias y métricas de rendimiento.',
    39.99,
    'EUR',
    90,
    1
),
(
    'ingles-conversacional',
    'Inglés Conversacional',
    'Mejora tu fluidez en inglés con clases prácticas, retos semanales y recursos de pronunciación. Practiva conversación real con feedback del chatbot y acceso a biblioteca de recursos.',
    29.99,
    'EUR',
    60,
    1
),
(
    'programacion-web-desde-cero',
    'Programación Web desde Cero',
    'Aprende HTML, CSS y JavaScript con proyectos guiados y seguimiento del progreso. Retos de codificación, entregas evaluadas y portal de proyectos personales con insignias por hito completado.',
    49.99,
    'EUR',
    120,
    1
),
(
    'nutricion-deportiva',
    'Nutrición Deportiva',
    'Organiza tus comidas, registra sensaciones diarias y recibe recomendaciones adaptadas mediante el chatbot nutricional. Planes personalizados y seguimiento en tiempo real.',
    34.99,
    'EUR',
    75,
    1
),
(
    'introduccion-python',
    'Introducción a Python',
    'Fundamentos de programación con Python. Desde variables y funciones hasta manejo de librerías. Incluye retos prácticos y proyecto final.',
    39.99,
    'EUR',
    80,
    1
);

-- Asignar profesores a cursos
-- Curso 1: Entrenamiento Personal → Laura Martín (ID 1)
INSERT INTO course_teachers (course_id, user_id) VALUES
(1, 1);

-- Curso 2: Inglés Conversacional → James Walker (ID 2)
INSERT INTO course_teachers (course_id, user_id) VALUES
(2, 2);

-- Curso 3: Programación Web → Claudia Pérez (ID 3)
INSERT INTO course_teachers (course_id, user_id) VALUES
(3, 3);

-- Curso 4: Nutrición Deportiva → Adrián Gómez (ID 4)
INSERT INTO course_teachers (course_id, user_id) VALUES
(4, 4);

-- Curso 5: Python → Claudia Pérez (ID 3)
INSERT INTO course_teachers (course_id, user_id) VALUES
(5, 3);

-- Insertar insignias de ejemplo
INSERT INTO badges (code, name, description, image_url, is_active) VALUES
('first-steps', 'Primeros Pasos', 'Completaste tu primer día en el curso', '/assets/img/badges/first-steps.svg', 1),
('week-warrior', 'Guerrero de la Semana', 'Completaste una semana completa con racha diaria', '/assets/img/badges/week-warrior.svg', 1),
('course-master', 'Maestro del Curso', 'Completaste el curso al 100%', '/assets/img/badges/course-master.svg', 1),
('nutrition-tracker', 'Nutricionista Personal', 'Registraste más de 30 días de comidas', '/assets/img/badges/nutrition-tracker.svg', 1),
('code-warrior', 'Guerrero del Código', 'Completaste 10 retos de programación', '/assets/img/badges/code-warrior.svg', 1);

-- Configurar chatbot por curso
-- Curso 1: Entrenamiento → Fitness Module
INSERT INTO chatbot_configs (course_id, module_key, system_prompt, welcome_prompt, is_enabled, temperature) VALUES
(
    1,
    'fitness',
    'Eres un asistente especializado en entrenamiento personal y nutrición. Proporciona consejos prácticos, analiza progresos y motiva al usuario a mantener la racha diaria.',
    '¡Hola! Soy tu asistente de entrenamiento. ¿Cómo te has sentido hoy? ¿Necesitas que analice tus comidas o tienes alguna duda sobre tu plan?',
    1,
    0.75
);

-- Curso 2: Idiomas → Languages Module
INSERT INTO chatbot_configs (course_id, module_key, system_prompt, welcome_prompt, is_enabled, temperature) VALUES
(
    2,
    'languages',
    'Eres un profesor de inglés conversacional. Ayuda al usuario a practicar conversación, corrige errores de forma amable y proporciona feedback constructivo.',
    'Welcome! I\'m your English conversation partner. Let\'s practice together today. What would you like to talk about?',
    1,
    0.80
);

-- Curso 3: Programación → Programming Module
INSERT INTO chatbot_configs (course_id, module_key, system_prompt, welcome_prompt, is_enabled, temperature) VALUES
(
    3,
    'programming',
    'Eres un mentor de programación web experto en HTML, CSS y JavaScript. Ayuda a resolver dudas, revisa código y proporciona mejoras.',
    '¡Hola desarrollador! ¿En qué reto estás trabajando? ¿Necesitas ayuda con tu código?',
    1,
    0.70
);

-- Curso 4: Nutrición → Nutrition Module
INSERT INTO chatbot_configs (course_id, module_key, system_prompt, welcome_prompt, is_enabled, temperature) VALUES
(
    4,
    'nutrition',
    'Eres un experto en nutrición deportiva. Analiza los registros de comidas del usuario, evalúa macros y proporciona recomendaciones personalizadas.',
    '¡Bienvenido! Hoy vamos a revisar tu nutrición. ¿Has registrado lo que has comido? Cuéntame para analizarlo.',
    1,
    0.75
);

-- Curso 5: Python → Programming Module
INSERT INTO chatbot_configs (course_id, module_key, system_prompt, welcome_prompt, is_enabled, temperature) VALUES
(
    5,
    'programming',
    'Eres un mentor de Python. Ayuda a entender conceptos de programación, resuelve dudas sobre sintaxis y motiva la práctica constante.',
    '¡Hola! ¿En qué lección estás? ¿Tienes dudas sobre Python?',
    1,
    0.70
);

-- Nota sobre las contraseñas:
-- Todas las contraseñas de ejemplo están hasheadas con bcrypt (password: "password")
-- En producción, debes cambiar estas credenciales inmediatamente.
