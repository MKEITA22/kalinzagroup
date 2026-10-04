-- KALINZA GROUP — Schéma de base de données
-- Compatible MySQL 5.7+ / MariaDB (Hostinger)
-- Charset UTF-8 pour le français

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------
-- Utilisateurs de l'administration
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120)        NOT NULL,
    email         VARCHAR(160)        NOT NULL UNIQUE,
    password_hash VARCHAR(255)        NOT NULL,
    role          ENUM('admin','editor') NOT NULL DEFAULT 'editor',
    is_active     TINYINT(1)          NOT NULL DEFAULT 1,
    created_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- Services (Imprimerie & Prod)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS services (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    division     ENUM('imprimerie','prod','digital') NOT NULL,
    category     VARCHAR(100)        NOT NULL,
    title        VARCHAR(150)        NOT NULL,
    slug         VARCHAR(160)        NOT NULL UNIQUE,
    description  TEXT                NULL,
    price_note   VARCHAR(255)        NULL,      -- ex : "à partir de ..." ou "sur devis"
    min_quantity VARCHAR(60)         NULL,      -- ex : "50 exemplaires", "1 séance"
    delivery_time VARCHAR(100)       NULL,      -- ex : "3 à 5 jours ouvrés"
    cover_image  VARCHAR(255)        NULL,
    is_visible   TINYINT(1)          NOT NULL DEFAULT 1,
    sort_order   SMALLINT UNSIGNED   NOT NULL DEFAULT 0,
    created_at   DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- Portfolio / Galerie
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS portfolio_items (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category      ENUM('photo','video','imprimerie','design') NOT NULL,
    title         VARCHAR(150)        NOT NULL,
    description   TEXT                NULL,
    media_url     VARCHAR(255)        NOT NULL,
    thumbnail_url VARCHAR(255)        NULL,
    is_featured   TINYINT(1)          NOT NULL DEFAULT 0,
    sort_order    SMALLINT UNSIGNED   NOT NULL DEFAULT 0,
    created_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- Demandes de devis
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS quote_requests (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name      VARCHAR(150)        NOT NULL,
    company        VARCHAR(150)        NULL,
    phone          VARCHAR(30)         NOT NULL,
    email          VARCHAR(160)        NULL,
    division       ENUM('imprimerie','prod','digital') NOT NULL,
    service_id     INT UNSIGNED        NULL,
    service_type   VARCHAR(150)        NULL,     -- libellé libre si hors liste
    description    TEXT                NOT NULL,
    quantity       VARCHAR(50)         NULL,
    dimensions     VARCHAR(100)        NULL,
    desired_date   DATE                NULL,
    attachment_path VARCHAR(255)       NULL,
    status         ENUM('nouveau','en_cours','traite','refuse') NOT NULL DEFAULT 'nouveau',
    admin_note     TEXT                NULL,
    created_at     DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_quote_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- Créneaux de disponibilité (séances photo/vidéo)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS availability_slots (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slot_date    DATE                NOT NULL,
    slot_time    TIME                NOT NULL,
    is_available TINYINT(1)          NOT NULL DEFAULT 1,
    UNIQUE KEY uniq_slot (slot_date, slot_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- Réservations de séances (Kalinza Prod)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS bookings (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_type   VARCHAR(150)        NOT NULL,   -- ex : portrait, événement, corporate...
    slot_id        INT UNSIGNED        NULL,
    requested_date DATE                NOT NULL,
    requested_time TIME                NULL,
    full_name      VARCHAR(150)        NOT NULL,
    phone          VARCHAR(30)         NOT NULL,
    email          VARCHAR(160)        NULL,
    notes          TEXT                NULL,
    status         ENUM('en_attente','confirmee','reportee','annulee') NOT NULL DEFAULT 'en_attente',
    admin_note     TEXT                NULL,
    created_at     DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_booking_slot FOREIGN KEY (slot_id) REFERENCES availability_slots(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- Messages de contact
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contact_messages (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(150)        NOT NULL,
    email      VARCHAR(160)        NULL,
    phone      VARCHAR(30)         NULL,
    message    TEXT                NOT NULL,
    status     ENUM('nouveau','lu','traite') NOT NULL DEFAULT 'nouveau',
    created_at DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- Contenus éditables des pages (textes gérables depuis l'admin)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS page_contents (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_key      VARCHAR(80)         NOT NULL,
    section_key   VARCHAR(80)         NOT NULL,
    content_type  ENUM('text','html','image') NOT NULL DEFAULT 'text',
    content_value TEXT                NULL,
    UNIQUE KEY uniq_page_section (page_key, section_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- Réglages généraux du site (coordonnées, réseaux sociaux, SEO)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS site_settings (
    setting_key   VARCHAR(80)  PRIMARY KEY,
    setting_value TEXT         NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO site_settings (setting_key, setting_value) VALUES
    ('site_name', 'Kalinza Group'),
    ('phone_primary', ''),
    ('whatsapp_number', ''),
    ('email_contact', ''),
    ('address', ''),
    ('facebook_url', ''),
    ('instagram_url', ''),
    ('meta_title_default', 'Kalinza Group — Imprimerie, Studio Photo/Vidéo & Digital'),
    ('meta_description_default', 'Kalinza Group réunit Kalinza Imprimerie, Kalinza Prod et Kalinza Digital : impression, communication visuelle, studio photo, production vidéo, sites web et applications mobiles à Abidjan.')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

SET FOREIGN_KEY_CHECKS = 1;
