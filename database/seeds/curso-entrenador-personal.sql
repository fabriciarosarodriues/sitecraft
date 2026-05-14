-- ============================================================
-- SEED: Curso Online Entrenador Personal Nivel I
-- Ejecutar DESPUÉS de schema.sql e initial-data.sql
-- ============================================================

USE sitecraft;

-- -------------------------------------------------------
-- 1. CURSO PRINCIPAL
-- -------------------------------------------------------
INSERT INTO courses (slug, title, description, price, currency, duration_days, is_published, css_file) VALUES
(
    'entrenador-personal-nivel-i',
    'Curso Online Entrenador Personal Nivel I',
    'El Curso Online de Entrenador/a Personal Nivel I: Bases del Entrenamiento en Sala de Fitness y Musculación está diseñado y adaptado basándonos en la experiencia de 30 años formando profesionales del Fitness y el Ejercicio Físico. Modalidad 100% online con tutores personalizados, vídeos, gráficas e ilustraciones.',
    890.00,
    'EUR',
    60,
    1,
    'assets/css/pages/courses/entrenador-personal-nivel-i.css'
);

-- Capturamos el ID recién insertado
SET @course_id = LAST_INSERT_ID();

-- -------------------------------------------------------
-- 2. ALUMNOS DE PRUEBA PARA TESTING DEL CURSO
-- -------------------------------------------------------
-- Contraseña en texto plano para todos: password
INSERT INTO users (full_name, email, password_hash, phone, is_active) VALUES
('Ana López', 'ana.lopez@sitecraft.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '611101001', 1),
('Carlos Ruiz', 'carlos.ruiz@sitecraft.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '611101002', 1),
('Marta Sánchez', 'marta.sanchez@sitecraft.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '611101003', 1),
('Diego Fernández', 'diego.fernandez@sitecraft.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '611101004', 1)
ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    phone = VALUES(phone),
    is_active = VALUES(is_active);

-- Asignar rol student a los alumnos de prueba
INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
JOIN roles r ON r.code = 'student'
WHERE u.email IN (
    'ana.lopez@sitecraft.local',
    'carlos.ruiz@sitecraft.local',
    'marta.sanchez@sitecraft.local',
    'diego.fernandez@sitecraft.local'
);

-- Matricular alumnos de prueba con estados diferentes para testear UI
INSERT INTO enrollments (user_id, course_id, enrollment_status, started_at, ends_at)
SELECT
    u.id,
    @course_id,
    CASE
        WHEN u.email = 'ana.lopez@sitecraft.local' THEN 'active'
        WHEN u.email = 'carlos.ruiz@sitecraft.local' THEN 'pending_payment'
        WHEN u.email = 'marta.sanchez@sitecraft.local' THEN 'completed'
        WHEN u.email = 'diego.fernandez@sitecraft.local' THEN 'paused'
    END AS enrollment_status,
    CASE
        WHEN u.email = 'carlos.ruiz@sitecraft.local' THEN NULL
        ELSE NOW()
    END AS started_at,
    CASE
        WHEN u.email = 'carlos.ruiz@sitecraft.local' THEN NULL
        ELSE DATE_ADD(NOW(), INTERVAL 60 DAY)
    END AS ends_at
FROM users u
WHERE u.email IN (
    'ana.lopez@sitecraft.local',
    'carlos.ruiz@sitecraft.local',
    'marta.sanchez@sitecraft.local',
    'diego.fernandez@sitecraft.local'
)
ON DUPLICATE KEY UPDATE
    enrollment_status = VALUES(enrollment_status),
    started_at = VALUES(started_at),
    ends_at = VALUES(ends_at);

-- -------------------------------------------------------
-- 3. PROFESOR DEL CURSO (Laura Martín, id=1 del seed inicial)
-- -------------------------------------------------------
INSERT INTO course_teachers (course_id, user_id) VALUES (@course_id, 1);

-- -------------------------------------------------------
-- 4. CATEGORÍAS
-- -------------------------------------------------------
INSERT INTO course_category_map (course_id, category_id)
SELECT @course_id, id FROM course_categories WHERE slug IN ('cursos', 'fitness-entrenamiento');

-- -------------------------------------------------------
-- 4. MEDIA DE PORTADA DEL CURSO
-- -------------------------------------------------------
INSERT INTO course_media (course_id, lesson_id, type, title, description, url, thumbnail_url, sort_order) VALUES
(
    @course_id, NULL, 'image',
    'Portada – Sala de musculación profesional',
    'Fotografía cenital de una sala de musculación moderna con máquinas de cable, mancuernas ordenadas en rack cromado, suelo de goma negra y luz cenital blanca. Al fondo hay un espejo de pared completa con un entrenador personal observando a un alumno realizando sentadilla con barra.',
    'assets/uploads/courses/entrenador-personal-nivel-i/portada-sala-musculacion.png',
    'assets/uploads/courses/entrenador-personal-nivel-i/portada-sala-musculacion.png',
    1
),
(
    @course_id, NULL, 'image',
    'Banner – Profesional del fitness en acción',
    'Fotografía en plano medio de un entrenador personal (mujer, 30 años, ropa deportiva oscura con logo SITECRAFT) sujetando una tablet y supervisando a una alumna que realiza peso muerto con barra olímpica. Fondo desenfocado de sala de pesas con carteles motivacionales en tonos negro y verde lima.',
    'assets/uploads/courses/entrenador-personal-nivel-i/banner-entrenador-alumna.png',
    'assets/uploads/courses/entrenador-personal-nivel-i/banner-entrenador-alumna.png',
    2
),
(
    @course_id, NULL, 'video',
    'Vídeo de presentación del curso',
    'Vídeo de presentación de 2 minutos con Laura Martín frente a cámara en una sala de fitness. Explica en qué consiste el curso, qué aprenderás, la estructura en 5 bloques y cómo funciona el sistema de tutoría. Al final aparece un reel de imágenes de alumnos entrenando con música motivacional de fondo.',
    'assets/uploads/courses/entrenador-personal-nivel-i/presentacion.mp4',
    'assets/uploads/courses/entrenador-personal-nivel-i/video-presentacion.png',
    3
),
(
    @course_id, NULL, 'image',
    'Infografía – ¿A quién va dirigido?',
    'Infografía vertical con fondo degradado negro-verde lima. Muestra 4 iconos (persona sin experiencia previa, deportista amateur, monitor de gym, profesional que quiere titularse) con textos breves en blanco. Pie de imagen: "Sin requisitos previos de acceso".',
    'assets/uploads/courses/entrenador-personal-nivel-i/infografia-dirigido.png',
    'assets/uploads/courses/entrenador-personal-nivel-i/infografia-dirigido.png',
    4
),
(
    @course_id, NULL, 'image',
    'Certificado de ejemplo – Acreditación ANEF',
    'Mockup de certificado oficial en formato A4 horizontal, fondo blanco con bordes dorados, logotipo SITECRAFT en esquina superior derecha, texto "Entrenador/a Personal Nivel I" en tipografía serif negra, campo de nombre del alumno, fecha de expedición y firma digital del director académico.',
    'assets/uploads/courses/entrenador-personal-nivel-i/certificado-ejemplo.png',
    'assets/uploads/courses/entrenador-personal-nivel-i/certificado-ejemplo.png',
    5
);

-- -------------------------------------------------------
-- 5. MÓDULOS FORMATIVOS ESPECÍFICOS (Bloque 1 – 5 módulos base)
-- -------------------------------------------------------
INSERT INTO course_modules (course_id, title, description, sort_order) VALUES
(@course_id, 'BLOQUE 1 – Módulos Formativos Específicos',      'Los 5 módulos fundamentales sobre bases del ejercicio físico saludable aplicados al fitness.', 1),
(@course_id, 'BLOQUE 2 – Evaluación de la Condición Física',   'Tests, pruebas, cuestionarios y valoración antropométrica y postural del cliente.', 2),
(@course_id, 'BLOQUE 3 – Planificación y Prescripción',        'Diseño de programas de entrenamiento, planificación de actividades y prescripción del ejercicio.', 3),
(@course_id, 'BLOQUE 4 – Intervención en Sala Polivalente',    'Metodología, recursos, seguridad y biomecánica aplicada al acondicionamiento físico en sala.', 4),
(@course_id, 'BLOQUE 5 – Gestión y Profesionalización',        'Gestión de salas, organización de eventos, evaluación de programas y desarrollo profesional.', 5);

SET @mod1 = (SELECT id FROM course_modules WHERE course_id = @course_id AND sort_order = 1);
SET @mod2 = (SELECT id FROM course_modules WHERE course_id = @course_id AND sort_order = 2);
SET @mod3 = (SELECT id FROM course_modules WHERE course_id = @course_id AND sort_order = 3);
SET @mod4 = (SELECT id FROM course_modules WHERE course_id = @course_id AND sort_order = 4);
SET @mod5 = (SELECT id FROM course_modules WHERE course_id = @course_id AND sort_order = 5);

-- -------------------------------------------------------
-- 6. LECCIONES – BLOQUE 1
-- -------------------------------------------------------
INSERT INTO course_lessons (module_id, title, body, sort_order, duration_minutes, is_free_preview) VALUES
(
    @mod1,
    'Introducción al Fitness',
    '<h2>Introducción al Fitness</h2>
    <p>El fitness engloba el conjunto de actividades físicas orientadas a mejorar la salud, la condición física y el bienestar general de la persona. En esta lección conocerás la historia del fitness moderno, sus objetivos y el papel del entrenador personal como guía profesional.</p>
    <h3>Contenidos</h3>
    <ul>
        <li>Evolución histórica del fitness y la musculación.</li>
        <li>Diferencias entre fitness, wellness y deporte de rendimiento.</li>
        <li>El rol del Entrenador Personal Nivel I según el marco europeo EQF.</li>
        <li>Ética y responsabilidad profesional.</li>
    </ul>
    <p><strong>Recurso:</strong> PDF descargable con glosario de términos del fitness.</p>',
    1, 45, 1
),
(
    @mod1,
    'Bases del ejercicio saludable',
    '<h2>Bases del ejercicio saludable</h2>
    <p>Aprenderás los principios científicos que rigen la adaptación del organismo al ejercicio físico, fundamentales para diseñar programas eficaces y seguros.</p>
    <h3>Principios del entrenamiento</h3>
    <ul>
        <li>Principio de sobrecarga progresiva.</li>
        <li>Principio de especificidad.</li>
        <li>Principio de reversibilidad.</li>
        <li>Principio de variedad y periodización.</li>
    </ul>
    <h3>Variables del entrenamiento</h3>
    <p>Volumen, intensidad, densidad, frecuencia y selección de ejercicios.</p>',
    2, 60, 0
),
(
    @mod1,
    'Ejercicio neuromuscular saludable',
    '<h2>Ejercicio neuromuscular saludable</h2>
    <p>Estudio del sistema nervioso y muscular en el contexto del ejercicio. Aprenderás cómo el músculo se contrae, qué tipos de fibras existen y cómo seleccionar ejercicios según el objetivo del cliente.</p>
    <h3>Tipos de contracción muscular</h3>
    <ul>
        <li>Contracción isotónica: concéntrica y excéntrica.</li>
        <li>Contracción isométrica.</li>
        <li>Contracción isocinética.</li>
    </ul>
    <h3>Ejercicios clave</h3>
    <p>Sentadilla, peso muerto, press banca, dominadas: análisis biomecánico completo con ilustraciones de músculos activados.</p>',
    3, 75, 0
),
(
    @mod1,
    'Ejercicio cardiovascular saludable',
    '<h2>Ejercicio cardiovascular saludable</h2>
    <p>El trabajo cardiovascular es fundamental en cualquier programa de fitness. Esta lección cubre los sistemas energéticos, las zonas de frecuencia cardíaca y los métodos de entrenamiento aeróbico y anaeróbico.</p>
    <h3>Sistemas energéticos</h3>
    <ul>
        <li>Sistema ATP-PC (fosfágenos).</li>
        <li>Sistema glucolítico (anaeróbico láctico).</li>
        <li>Sistema oxidativo (aeróbico).</li>
    </ul>
    <h3>Métodos cardiovasculares</h3>
    <p>Continuo extensivo, continuo intensivo, interválico (HIIT), Fartlek y entrenamiento en circuito.</p>',
    4, 60, 0
),
(
    @mod1,
    'Amplitud de movimiento saludable',
    '<h2>Amplitud de movimiento saludable</h2>
    <p>La flexibilidad y la movilidad articular son pilares de la salud musculoesquelética. Aprenderás a evaluar y mejorar la amplitud de movimiento en tus clientes mediante técnicas de estiramiento y movilidad.</p>
    <h3>Técnicas de estiramiento</h3>
    <ul>
        <li>Estático activo y pasivo.</li>
        <li>Dinámico y balístico.</li>
        <li>FNP (Facilitación Neuromuscular Propioceptiva).</li>
    </ul>
    <h3>Movilidad articular</h3>
    <p>Cadera, tobillo, columna torácica y hombros: ejercicios de movilización específica con vídeos demostrativos.</p>',
    5, 50, 0
);

-- -------------------------------------------------------
-- 7. LECCIONES – BLOQUE 2
-- -------------------------------------------------------
INSERT INTO course_lessons (module_id, title, body, sort_order, duration_minutes) VALUES
(
    @mod2,
    'Funciones orgánicas y fatiga física en el Fitness',
    '<p>Análisis fisiológico de los sistemas cardiovascular, respiratorio y muscular durante el ejercicio. Comprensión de la fatiga aguda y crónica y su impacto en la programación del entrenamiento.</p>',
    1, 55
),
(
    @mod2,
    'Tests y cuestionarios de evaluación inicial',
    '<p>Aprenderás a aplicar el cuestionario PAR-Q, entrevistas motivacionales y tests de aptitud física general para establecer la línea de base del cliente antes de comenzar el entrenamiento.</p>',
    2, 50
),
(
    @mod2,
    'Valoración antropométrica',
    '<p>Medición de peso, talla, perímetros corporales, pliegues cutáneos y composición corporal mediante plicometría y fórmulas validadas (Durnin-Womersley, Jackson-Pollock). Práctica con casos reales simulados.</p>',
    3, 65
),
(
    @mod2,
    'Pruebas biológico-funcionales',
    '<p>Test de Ruffier-Dickson (capacidad cardiovascular), Test de Astrand (VO2 max estimado), dinamometría manual y lumbar, test de fuerza isométrica.</p>',
    4, 60
),
(
    @mod2,
    'Tests de campo para la condición física',
    '<p>Test de Course-Navette, Test de Cooper, Test de Rockport, Test de sentadillas y flexiones en 1 minuto. Tablas de referencia por edad y sexo.</p>',
    5, 55
),
(
    @mod2,
    'Valoración postural',
    '<p>Evaluación visual y con plomada de la postura estática en plano frontal, sagital y transversal. Detección de alteraciones posturales comunes: hipercifosis, hiperlordosis, escoliosis funcional.</p>',
    6, 60
),
(
    @mod2,
    'La entrevista personal y análisis de datos',
    '<p>Técnicas de comunicación efectiva con el cliente, escucha activa, establecimiento de objetivos SMART y registro de resultados en fichas de evaluación digital.</p>',
    7, 45
);

-- -------------------------------------------------------
-- 8. LECCIONES – BLOQUE 3
-- -------------------------------------------------------
INSERT INTO course_lessons (module_id, title, body, sort_order, duration_minutes) VALUES
(
    @mod3,
    'Planificación de actividades de fitness',
    '<p>Diseño de la macrociclo, mesociclo y microciclo. Periodización lineal, ondulada y por bloques aplicada al entrenamiento en sala de fitness.</p>',
    1, 70
),
(
    @mod3,
    'Prescripción del ejercicio en sala polivalente',
    '<p>Criterios de selección de ejercicios, progresión de cargas (método 1RM, RPE, RIR), series, repeticiones y tiempos de pausa según objetivos: pérdida de grasa, hipertrofia, fuerza o resistencia.</p>',
    2, 80
),
(
    @mod3,
    'Gestión y coordinación de la sala de entrenamiento',
    '<p>Organización del espacio, gestión de turnos y capacidad, protocolos de higiene y mantenimiento de equipamiento. Comunicación con la dirección del centro deportivo.</p>',
    3, 45
);

-- -------------------------------------------------------
-- 9. LECCIONES – BLOQUE 4
-- -------------------------------------------------------
INSERT INTO course_lessons (module_id, title, body, sort_order, duration_minutes) VALUES
(
    @mod4,
    'Intervención metodológica en sala polivalente',
    '<p>Fases de la sesión de entrenamiento: calentamiento general, calentamiento específico, parte principal y vuelta a la calma. Técnicas de comunicación, corrección gestual y feedback al cliente.</p>',
    1, 65
),
(
    @mod4,
    'Recursos y materiales de la sesión',
    '<p>Uso correcto del material de sala: barras olímpicas, discos, mancuernas, kettlebells, bandas elásticas, TRX, máquinas de poleas y selectorizado. Guía de mantenimiento básico.</p>',
    2, 50
),
(
    @mod4,
    'Seguridad y prevención en sala',
    '<p>Protocolos de primeros auxilios básicos, actuación ante lesiones frecuentes (contracturas, esguinces, lipotimias), normativa de seguridad en instalaciones deportivas y responsabilidad civil del entrenador.</p>',
    3, 55
),
(
    @mod4,
    'Biomecánica aplicada al acondicionamiento físico',
    '<p>Análisis de la palanca ósea, vectores de fuerza, eje de movimiento y grupos musculares protagonistas en los ejercicios multiarticulares básicos. Aplicación práctica para prevenir lesiones y optimizar la técnica.</p>',
    4, 75
),
(
    @mod4,
    'Parámetros de prescripción por tipo de usuario',
    '<p>Adaptación del programa para poblaciones especiales: sedentarios, mayores de 60 años, personas con sobrepeso, mujeres en período postnatal y adolescentes. Consideraciones médicas y contraindicaciones.</p>',
    5, 60
);

-- -------------------------------------------------------
-- 10. LECCIONES – BLOQUE 5
-- -------------------------------------------------------
INSERT INTO course_lessons (module_id, title, body, sort_order, duration_minutes) VALUES
(
    @mod5,
    'Organización de eventos en salas de entrenamiento',
    '<p>Planificación de retos, competiciones internas, open days y semanas de entrenamiento especiales. Gestión de grupos, comunicación y marketing básico para entrenadores personales autónomos.</p>',
    1, 45
),
(
    @mod5,
    'Evaluación del programa y evolución de sesiones',
    '<p>Indicadores de progreso objetivos y subjetivos, herramientas de seguimiento digital, interpretación de resultados y reajuste del programa según la evolución del cliente. Cierre del ciclo y fidelización.</p>',
    2, 50
);

-- -------------------------------------------------------
-- 11. MEDIA POR LECCIÓN (imágenes y vídeos descriptivos)
-- -------------------------------------------------------

-- Recuperar IDs de lecciones del bloque 1
SET @les_intro      = (SELECT id FROM course_lessons WHERE module_id = @mod1 AND sort_order = 1);
SET @les_bases      = (SELECT id FROM course_lessons WHERE module_id = @mod1 AND sort_order = 2);
SET @les_neuro      = (SELECT id FROM course_lessons WHERE module_id = @mod1 AND sort_order = 3);
SET @les_cardio     = (SELECT id FROM course_lessons WHERE module_id = @mod1 AND sort_order = 4);
SET @les_amplitud   = (SELECT id FROM course_lessons WHERE module_id = @mod1 AND sort_order = 5);

-- Recuperar IDs de lecciones del bloque 2
SET @les_fatiga     = (SELECT id FROM course_lessons WHERE module_id = @mod2 AND sort_order = 1);
SET @les_tests      = (SELECT id FROM course_lessons WHERE module_id = @mod2 AND sort_order = 2);
SET @les_antrop     = (SELECT id FROM course_lessons WHERE module_id = @mod2 AND sort_order = 3);
SET @les_postura    = (SELECT id FROM course_lessons WHERE module_id = @mod2 AND sort_order = 6);

-- Recuperar IDs de lecciones del bloque 3
SET @les_planif     = (SELECT id FROM course_lessons WHERE module_id = @mod3 AND sort_order = 1);
SET @les_prescr     = (SELECT id FROM course_lessons WHERE module_id = @mod3 AND sort_order = 2);

-- Recuperar IDs de lecciones del bloque 4
SET @les_biom       = (SELECT id FROM course_lessons WHERE module_id = @mod4 AND sort_order = 4);
SET @les_segur      = (SELECT id FROM course_lessons WHERE module_id = @mod4 AND sort_order = 3);

INSERT INTO course_media (course_id, lesson_id, type, title, description, url, thumbnail_url, duration_seconds, sort_order) VALUES

-- Vídeo: Introducción al fitness
(
    @course_id, @les_intro, 'video',
    'Vídeo – Historia y evolución del fitness moderno',
    'Vídeo de 8 minutos con línea de tiempo animada (motion graphics) que recorre la historia del culturismo y el fitness desde los años 50 hasta hoy. Incluye imágenes de archivo (gimnasios de los 70, aerobic de los 80, crossfit actual) y narración de Laura Martín en off.',
    'assets/uploads/courses/entrenador-personal-nivel-i/historia-fitness.mp4',
    'assets/uploads/courses/entrenador-personal-nivel-i/video-historia-fitness.png',
    480, 1
),
-- Imagen: pirámide del fitness
(
    @course_id, @les_intro, 'image',
    'Infografía – Pirámide de la condición física',
    'Pirámide de 4 niveles con fondo blanco y colores degradados de verde claro a verde oscuro. Nivel base: actividad diaria. Nivel 2: ejercicio cardiovascular. Nivel 3: fuerza y tonificación. Cúspide: flexibilidad y recuperación. Iconos minimalistas y tipografía Inter.',
    'assets/uploads/courses/entrenador-personal-nivel-i/piramide-condicion-fisica.png',
    'assets/uploads/courses/entrenador-personal-nivel-i/piramide-condicion-fisica.png',
    NULL, 2
),

-- Vídeo: Bases del ejercicio
(
    @course_id, @les_bases, 'video',
    'Vídeo – Principios del entrenamiento explicados',
    'Vídeo de 12 minutos. Primera mitad: presentación animada de los 4 principios del entrenamiento con ejemplos visuales (gráficas de progresión de carga, comparativas de atletas). Segunda mitad: Laura Martín en sala de pesas mostrando ejemplos reales de sobrecarga progresiva con una alumna.',
    'assets/uploads/courses/entrenador-personal-nivel-i/principios-entrenamiento.mp4',
    'assets/uploads/courses/entrenador-personal-nivel-i/video-principios.png',
    720, 1
),
(
    @course_id, @les_bases, 'image',
    'Tabla – Variables del entrenamiento',
    'Tabla con fondo oscuro (#1a1a1a) y texto blanco. 6 filas: Volumen (series × reps), Intensidad (%1RM o RPE), Densidad (relación trabajo/descanso), Frecuencia (días/semana), Selección de ejercicios, Orden de ejercicios. Cada fila con un icono verde lima y una descripción breve.',
    'assets/uploads/courses/entrenador-personal-nivel-i/tabla-variables-entrenamiento.png',
    'assets/uploads/courses/entrenador-personal-nivel-i/tabla-variables.png',
    NULL, 2
),
(
    @course_id, @les_bases, 'pdf',
    'PDF – Tabla de periodización anual',
    'PDF de 4 páginas. Portada con branding SITECRAFT. Página 2: tabla de macrociclo anual con bloques de hipertrofia, fuerza, potencia y descarga. Página 3: ejemplo de mesociclo de 4 semanas. Página 4: plantilla en blanco para rellenar con el cliente.',
    'assets/uploads/courses/entrenador-personal-nivel-i/pdfs/periodizacion-anual.pdf',
    'assets/uploads/courses/entrenador-personal-nivel-i/pdf-periodizacion.png',
    NULL, 3
),

-- Vídeo: Sistema neuromuscular
(
    @course_id, @les_neuro, 'video',
    'Vídeo – Tipos de contracción muscular con demostración práctica',
    'Vídeo de 15 minutos en sala de musculación. Primeros 5 min: animación 3D de la fibra muscular contrayéndose (actina/miosina). Siguientes 10 min: Laura Martín y un alumno demuestran curl de bíceps a diferentes velocidades distinguiendo fase concéntrica, excéntrica e isométrica. Música instrumental de fondo.',
    'assets/uploads/courses/entrenador-personal-nivel-i/tipos-contraccion.mp4',
    'assets/uploads/courses/entrenador-personal-nivel-i/video-contraccion.png',
    900, 1
),
(
    @course_id, @les_neuro, 'image',
    'Ilustración – Músculos activados en sentadilla',
    'Ilustración anatómica frontal y lateral del cuerpo humano realizando sentadilla con barra olímpica. Músculos activos coloreados en rojo (cuádriceps, glúteos, isquiosurales, erectores del raquis, core). Fondo blanco, etiquetas en negro con líneas finas. Estilo médico-científico.',
    'assets/uploads/courses/entrenador-personal-nivel-i/anatomia-sentadilla.png',
    'assets/uploads/courses/entrenador-personal-nivel-i/anatomia-sentadilla.png',
    NULL, 2
),
(
    @course_id, @les_neuro, 'image',
    'Ilustración – Músculos activados en peso muerto',
    'Ilustración anatómica de vista posterior del cuerpo humano realizando peso muerto convencional. Músculos activos: trapecio, romboides, dorsales, glúteos, isquiosurales, gemelos. Colores: naranja para músculos principales, amarillo para sinergistas. Fondo gris claro.',
    'assets/uploads/courses/entrenador-personal-nivel-i/anatomia-peso-muerto.png',
    'assets/uploads/courses/entrenador-personal-nivel-i/anatomia-peso-muerto.png',
    NULL, 3
),

-- Vídeo: Cardiovascular
(
    @course_id, @les_cardio, 'video',
    'Vídeo – Sistemas energéticos y zonas de frecuencia cardíaca',
    'Vídeo animado de 10 minutos con gráficas de barras que muestran el porcentaje de participación de cada sistema energético según la duración e intensidad del ejercicio. Incluye tabla de zonas FC (Z1-Z5) con colores y ejemplos de actividades para cada zona. Narración en off de Laura Martín.',
    'assets/uploads/courses/entrenador-personal-nivel-i/sistemas-energeticos.mp4',
    'assets/uploads/courses/entrenador-personal-nivel-i/video-sistemas-energeticos.png',
    600, 1
),
(
    @course_id, @les_cardio, 'image',
    'Infografía – Comparativa HIIT vs. Cardio continuo',
    'Infografía horizontal en dos columnas. Columna izquierda (fondo negro): HIIT — duración 20-30 min, alta intensidad, quema calórica postejercicio elevada. Columna derecha (fondo verde lima): Cardio continuo — duración 45-60 min, baja-media intensidad, beneficios cardiovasculares a largo plazo. Iconos de reloj, corazón y fuego.',
    'assets/uploads/courses/entrenador-personal-nivel-i/infografia-hiit-vs-continuo.png',
    'assets/uploads/courses/entrenador-personal-nivel-i/infografia-hiit.png',
    NULL, 2
),

-- Vídeo: Amplitud de movimiento
(
    @course_id, @les_amplitud, 'video',
    'Vídeo – Rutina de movilidad articular de 10 minutos',
    'Vídeo de 10 minutos con Laura Martín en ropa deportiva sobre esterilla en sala amplia y luminosa. Ejecuta secuencia completa: rotaciones de tobillo, movilidad de cadera (hip circles), apertura torácica con foam roller, extensión de columna, círculos de hombros. Cada ejercicio con contador de repeticiones en pantalla.',
    'assets/uploads/courses/entrenador-personal-nivel-i/rutina-movilidad-10min.mp4',
    'assets/uploads/courses/entrenador-personal-nivel-i/video-movilidad.png',
    600, 1
),
(
    @course_id, @les_amplitud, 'image',
    'Ilustración – Técnicas de estiramiento comparadas',
    'Tabla visual en 3 columnas con fondo blanco. Columna 1: estiramiento estático (figura estática manteniendo posición). Columna 2: estiramiento dinámico (figura con flecha circular indicando movimiento). Columna 3: FNP (figura con compañero aplicando resistencia). Cada columna con pros/contras en verde y rojo.',
    'assets/uploads/courses/entrenador-personal-nivel-i/comparativa-estiramientos.png',
    'assets/uploads/courses/entrenador-personal-nivel-i/comparativa-estiramientos.png',
    NULL, 2
),

-- Vídeo: Valoración antropométrica
(
    @course_id, @les_antrop, 'video',
    'Vídeo – Cómo medir pliegues cutáneos con plicómetro',
    'Vídeo de 18 minutos grabado en consulta deportiva. Laura Martín explica el protocolo de medición de 8 pliegues (Jackson-Pollock 7 y Durnin-Womersley). Se muestran planos de cámara detallados de cada punto anatómico. Incluye plantilla de registro en papel y cómo calcular el % graso con fórmulas.',
    'assets/uploads/courses/entrenador-personal-nivel-i/medicion-pliegues.mp4',
    'assets/uploads/courses/entrenador-personal-nivel-i/video-pliegues.png',
    1080, 1
),
(
    @course_id, @les_antrop, 'image',
    'Diagrama – Puntos anatómicos de medición plicométrica',
    'Figura humana de frente y perfil sobre fondo blanco con 8 puntos señalados con círculos rojos numerados: 1-Pectoral, 2-Axilar medio, 3-Tríceps, 4-Subescapular, 5-Abdominal, 6-Suprailíaco, 7-Muslo medio, 8-Pierna medial. Tabla lateral con referencia normal por sexo.',
    'assets/uploads/courses/entrenador-personal-nivel-i/diagrama-pliegues-cutaneos.png',
    'assets/uploads/courses/entrenador-personal-nivel-i/diagrama-pliegues.png',
    NULL, 2
),

-- Vídeo: Valoración postural
(
    @course_id, @les_postura, 'video',
    'Vídeo – Evaluación postural paso a paso',
    'Vídeo de 20 minutos en dos partes. Parte 1 (10 min): explicación con maniquí anatómico de las alteraciones posturales más comunes y sus implicaciones musculares. Parte 2 (10 min): demostración real con alumno voluntario, usando plomada y cuadrícula de fondo. Laura Martín explica cada hallazgo en cámara.',
    'assets/uploads/courses/entrenador-personal-nivel-i/evaluacion-postural-paso-paso.mp4',
    'assets/uploads/courses/entrenador-personal-nivel-i/video-postura.png',
    1200, 1
),

-- Vídeo: Planificación
(
    @course_id, @les_planif, 'video',
    'Vídeo – Cómo crear un programa de 12 semanas desde cero',
    'Screencast de 25 minutos en el que Laura Martín trabaja en tiempo real sobre una hoja de cálculo de planificación. Diseña un programa completo para un cliente ficticio (nube de datos: hombre, 35 años, sobrepeso ligero, objetivo estética). Se ven todas las decisiones: selección de ejercicios, volumen por día, progresión semana a semana.',
    'assets/uploads/courses/entrenador-personal-nivel-i/programa-12-semanas.mp4',
    'assets/uploads/courses/entrenador-personal-nivel-i/video-planificacion.png',
    1500, 1
),
(
    @course_id, @les_planif, 'pdf',
    'PDF – Plantilla de programa de entrenamiento 12 semanas',
    'PDF de 6 páginas con plantilla editable de programa de 12 semanas. Incluye: portada personalizable con logo, ficha del cliente, semanas 1-4 (acumulación), semanas 5-8 (intensificación), semanas 9-12 (realización/peak), semana 13 (descarga). En formato tabla con celdas para ejercicio, series, reps, carga y RIR.',
    'assets/uploads/courses/entrenador-personal-nivel-i/pdfs/plantilla-programa-12-semanas.pdf',
    'assets/uploads/courses/entrenador-personal-nivel-i/pdf-plantilla-programa.png',
    NULL, 2
),

-- Vídeo: Prescripción
(
    @course_id, @les_prescr, 'video',
    'Vídeo – Cómo calcular el 1RM y usarlo para prescribir carga',
    'Vídeo de 15 minutos. Primeros 7 min: explicación del test de 1RM directo e indirecto (fórmula de Epley, Brzycki). Siguientes 8 min: Laura en sala hace la prueba con una alumna en press de banca, mostrando el proceso completo, precauciones de seguridad y cómo registrar el resultado.',
    'assets/uploads/courses/entrenador-personal-nivel-i/calculo-1rm.mp4',
    'assets/uploads/courses/entrenador-personal-nivel-i/video-1rm.png',
    900, 1
),

-- Imágenes de biomecánica
(
    @course_id, @les_biom, 'image',
    'Ilustración – Análisis de palanca en press banca',
    'Ilustración técnica de perfil de un atleta en posición de press banca mostrando vectores de fuerza con flechas de colores: azul (peso externo), rojo (fuerza muscular), verde (reacción articular en hombro y codo). Ángulos de la palanca marcados con arcos y valores en grados.',
    'assets/uploads/courses/entrenador-personal-nivel-i/biomecanica-press-banca.png',
    'assets/uploads/courses/entrenador-personal-nivel-i/biomecanica-press-banca.png',
    NULL, 1
),
(
    @course_id, @les_biom, 'video',
    'Vídeo – Correcciones técnicas frecuentes en sala (Top 10)',
    'Vídeo de 22 minutos grabado en sala de musculación real. Formato comparativo (pantalla dividida): lado izquierdo muestra técnica incorrecta con resaltado rojo de la zona de riesgo; lado derecho muestra la técnica correcta en verde. Ejercicios: sentadilla, peso muerto, dominada, remo en polea, press militar, curl, extensión de tríceps, hip thrust, plancha y zancada.',
    'assets/uploads/courses/entrenador-personal-nivel-i/top10-correcciones-tecnicas.mp4',
    'assets/uploads/courses/entrenador-personal-nivel-i/video-correcciones.png',
    1320, 2
),

-- Seguridad
(
    @course_id, @les_segur, 'image',
    'Infografía – Protocolo de actuación ante lesión deportiva',
    'Diagrama de flujo vertical con fondo blanco y pasos numerados del 1 al 7: 1-Detener actividad, 2-Evaluar consciencia, 3-Aplicar RICE (Reposo, Hielo, Compresión, Elevación), 4-Valorar si pedir asistencia médica, 5-Registro del incidente, 6-Comunicar al cliente y centro, 7-Seguimiento. Iconos de primeros auxilios en rojo y verde.',
    'assets/uploads/courses/entrenador-personal-nivel-i/protocolo-lesion.png',
    'assets/uploads/courses/entrenador-personal-nivel-i/protocolo-lesion.png',
    NULL, 1
),
(
    @course_id, @les_segur, 'pdf',
    'PDF – Guía de primeros auxilios para entrenadores personales',
    'PDF de 10 páginas con fondo blanco y detalles en rojo corporativo. Incluye fichas laminables de: maniobra de Heimlich, RCP básico, atención a lipotimia, protocolo ante esguince y ante contractura severa. Validado por equipo médico deportivo. Recomendado imprimir y plastificar para tener en sala.',
    'assets/uploads/courses/entrenador-personal-nivel-i/pdfs/guia-primeros-auxilios.pdf',
    'assets/uploads/courses/entrenador-personal-nivel-i/pdf-primeros-auxilios.png',
    NULL, 2
);

-- -------------------------------------------------------
-- 12. CHATBOT CONFIG para este curso
-- -------------------------------------------------------
INSERT INTO chatbot_configs (course_id, module_key, system_prompt, welcome_prompt, is_enabled, temperature, max_tokens)
VALUES
(
    @course_id,
    'fitness',
    'Eres un asistente especializado en entrenamiento personal y fitness. Tienes conocimiento profundo del curso "Entrenador Personal Nivel I". Ayudas al alumno a entender los contenidos teóricos, resuelves dudas sobre biomecánica, planificación del entrenamiento, valoración física y nutrición deportiva básica. Proporciona respuestas claras, motivadoras y basadas en evidencia científica. Si la duda supera tu alcance, sugiere consultar con el tutor asignado.',
    '¡Hola! Soy tu asistente del Curso de Entrenador Personal Nivel I. ¿En qué lección estás trabajando? Puedo ayudarte con dudas de biomecánica, planificación, valoración física o cualquier concepto del programa.',
    1,
    0.72,
    1024
)
ON DUPLICATE KEY UPDATE
    module_key = VALUES(module_key),
    system_prompt = VALUES(system_prompt),
    welcome_prompt = VALUES(welcome_prompt),
    is_enabled = VALUES(is_enabled),
    temperature = VALUES(temperature),
    max_tokens = VALUES(max_tokens);

-- -------------------------------------------------------
-- FIN DEL SEED
-- -------------------------------------------------------
-- Resumen de lo insertado:
-- 1 curso publicado (890€, 60 días)
-- 4 alumnos de prueba creados (rol student)
-- 4 matrículas de prueba con estados variados
-- 5 módulos (bloques temáticos)
-- 19 lecciones con contenido HTML
-- 26 recursos multimedia (imágenes, vídeos y PDFs descriptivos)
-- 1 configuración de chatbot especializado
-- Categorías: Cursos + Fitness y entrenamiento
-- -------------------------------------------------------
