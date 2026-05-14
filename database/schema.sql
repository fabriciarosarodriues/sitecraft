SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE DATABASE IF NOT EXISTS sitecraft CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sitecraft;

-- ============================================================
-- DROP de todas las tablas (orden inverso a las FK)
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS course_category_map;
DROP TABLE IF EXISTS course_media;
DROP TABLE IF EXISTS course_lessons;
DROP TABLE IF EXISTS course_modules;
DROP TABLE IF EXISTS course_categories;
DROP TABLE IF EXISTS message_queue;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS chat_messages;
DROP TABLE IF EXISTS chatbot_configs;
DROP TABLE IF EXISTS nutrition_logs;
DROP TABLE IF EXISTS progress_summary;
DROP TABLE IF EXISTS attendance_logs;
DROP TABLE IF EXISTS study_logs;
DROP TABLE IF EXISTS user_badges;
DROP TABLE IF EXISTS badges;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS course_teachers;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;
-- ============================================================


CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(30) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE roles (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) NOT NULL,
    name VARCHAR(80) NOT NULL,
    UNIQUE KEY uq_roles_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_roles (
    user_id BIGINT UNSIGNED NOT NULL,
    role_id SMALLINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id),
    CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    KEY idx_user_roles_role_id (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE courses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    currency CHAR(3) NOT NULL DEFAULT 'EUR',
    duration_days INT UNSIGNED NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    css_file VARCHAR(220) NULL COMMENT 'Ruta CSS específica del curso, p.ej. assets/css/pages/courses/entrenador-personal-nivel-i.css',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_courses_slug (slug),
    KEY idx_courses_published (is_published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE course_teachers (
    course_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (course_id, user_id),
    CONSTRAINT fk_course_teachers_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_course_teachers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    KEY idx_course_teachers_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE enrollments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    enrollment_status ENUM('pending_payment','active','paused','cancelled','completed') NOT NULL DEFAULT 'pending_payment',
    started_at DATETIME NULL,
    ends_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_enrollments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_enrollments_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY uq_enrollments_user_course (user_id, course_id),
    KEY idx_enrollments_course_status (course_id, enrollment_status),
    KEY idx_enrollments_user_status (user_id, enrollment_status),
    CONSTRAINT chk_enrollments_dates CHECK (ends_at IS NULL OR started_at IS NULL OR ends_at >= started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enrollment_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'EUR',
    method ENUM('card','transfer','paypal','bizum','cash','other') NOT NULL DEFAULT 'card',
    payment_status ENUM('pending','paid','failed','refunded','expired') NOT NULL DEFAULT 'pending',
    external_reference VARCHAR(190) NULL,
    due_at DATETIME NULL,
    paid_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_enrollment FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE ON UPDATE CASCADE,
    KEY idx_payments_enrollment_status (enrollment_id, payment_status),
    KEY idx_payments_due_at (due_at),
    KEY idx_payments_external_ref (external_reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE badges (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(80) NOT NULL,
    name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    image_url VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_badges_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_badges (
    user_id BIGINT UNSIGNED NOT NULL,
    badge_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NULL,
    earned_at DATETIME NOT NULL,
    PRIMARY KEY (user_id, badge_id, earned_at),
    CONSTRAINT fk_user_badges_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_user_badges_badge FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_user_badges_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL ON UPDATE CASCADE,
    KEY idx_user_badges_badge (badge_id),
    KEY idx_user_badges_course (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE study_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enrollment_id BIGINT UNSIGNED NOT NULL,
    studied_on DATE NOT NULL,
    minutes_spent INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_study_logs_enrollment FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE ON UPDATE CASCADE,
    KEY idx_study_logs_enrollment_day (enrollment_id, studied_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attendance_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enrollment_id BIGINT UNSIGNED NOT NULL,
    attended_on DATE NOT NULL,
    present TINYINT(1) NOT NULL DEFAULT 1,
    source VARCHAR(40) NOT NULL DEFAULT 'app',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attendance_logs_enrollment FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_attendance_enrollment_day (enrollment_id, attended_on),
    KEY idx_attendance_logs_day (attended_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE progress_summary (
    enrollment_id BIGINT UNSIGNED PRIMARY KEY,
    total_minutes INT UNSIGNED NOT NULL DEFAULT 0,
    consecutive_days INT UNSIGNED NOT NULL DEFAULT 0,
    completion_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_progress_summary_enrollment FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE nutrition_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enrollment_id BIGINT UNSIGNED NOT NULL,
    logged_on DATE NOT NULL,
    meals_json JSON NOT NULL,
    mood VARCHAR(80) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_nutrition_logs_enrollment FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE ON UPDATE CASCADE,
    KEY idx_nutrition_logs_enrollment_day (enrollment_id, logged_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE chatbot_configs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    module_key VARCHAR(80) NOT NULL,
    system_prompt TEXT NULL,
    welcome_prompt TEXT NULL,
    is_enabled TINYINT(1) NOT NULL DEFAULT 1,
    max_tokens INT UNSIGNED NULL,
    temperature DECIMAL(3,2) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_chatbot_configs_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_chatbot_configs_course (course_id),
    KEY idx_chatbot_configs_module (module_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE chat_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NULL,
    enrollment_id BIGINT UNSIGNED NULL,
    message_role ENUM('user','assistant','system') NOT NULL,
    content MEDIUMTEXT NOT NULL,
    meta_json JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_chat_messages_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_chat_messages_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_chat_messages_enrollment FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE SET NULL ON UPDATE CASCADE,
    KEY idx_chat_messages_user_created (user_id, created_at),
    KEY idx_chat_messages_course_created (course_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(60) NOT NULL,
    channel ENUM('in_app','email','whatsapp') NOT NULL DEFAULT 'in_app',
    title VARCHAR(180) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('pending','sent','failed','read') NOT NULL DEFAULT 'pending',
    related_entity_type VARCHAR(40) NULL,
    related_entity_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sent_at DATETIME NULL,
    read_at DATETIME NULL,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    KEY idx_notifications_user_status (user_id, status),
    KEY idx_notifications_channel_status (channel, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE message_queue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    notification_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    channel ENUM('email','whatsapp') NOT NULL,
    payload_json JSON NOT NULL,
    queue_status ENUM('pending','processing','done','failed','cancelled') NOT NULL DEFAULT 'pending',
    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    max_attempts TINYINT UNSIGNED NOT NULL DEFAULT 5,
    available_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME NULL,
    last_error TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_message_queue_notification FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_message_queue_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    KEY idx_message_queue_status_available (queue_status, available_at),
    KEY idx_message_queue_channel_status (channel, queue_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Módulos de un curso (bloques temáticos)
CREATE TABLE course_modules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(220) NOT NULL,
    description TEXT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_course_modules_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE ON UPDATE CASCADE,
    KEY idx_course_modules_course_order (course_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lecciones dentro de un módulo
CREATE TABLE course_lessons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(220) NOT NULL,
    body LONGTEXT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    duration_minutes SMALLINT UNSIGNED NULL,
    is_free_preview TINYINT(1) NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_course_lessons_module FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE ON UPDATE CASCADE,
    KEY idx_course_lessons_module_order (module_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recursos multimedia (imágenes, vídeos, PDFs) de curso o lección
CREATE TABLE course_media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    lesson_id BIGINT UNSIGNED NULL,
    type ENUM('image','video','pdf','audio','link') NOT NULL DEFAULT 'image',
    title VARCHAR(220) NOT NULL,
    description TEXT NULL,
    url VARCHAR(500) NOT NULL,
    thumbnail_url VARCHAR(500) NULL,
    duration_seconds INT UNSIGNED NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_course_media_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_course_media_lesson FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE SET NULL ON UPDATE CASCADE,
    KEY idx_course_media_course_type (course_id, type),
    KEY idx_course_media_lesson (lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categorías de cursos
CREATE TABLE course_categories (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(80) NOT NULL,
    name VARCHAR(120) NOT NULL,
    UNIQUE KEY uq_course_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Relación curso-categoría (muchos a muchos)
CREATE TABLE course_category_map (
    course_id BIGINT UNSIGNED NOT NULL,
    category_id SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (course_id, category_id),
    CONSTRAINT fk_ccm_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ccm_category FOREIGN KEY (category_id) REFERENCES course_categories(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (code, name) VALUES
('admin', 'Administrador'),
('teacher', 'Profesor'),
('student', 'Alumno')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO course_categories (slug, name) VALUES
('cursos', 'Cursos'),
('fitness-entrenamiento', 'Fitness y entrenamiento'),
('idiomas', 'Idiomas'),
('programacion', 'Programación'),
('bienestar', 'Bienestar y nutrición')
ON DUPLICATE KEY UPDATE name = VALUES(name);
