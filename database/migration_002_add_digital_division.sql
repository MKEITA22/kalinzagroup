-- Migration 002 — Ajout de la division "Kalinza Digital" (développement web & applications mobiles)
-- À exécuter uniquement si vous avez déjà importé schema.sql avant cette mise à jour.
-- Exemple : mysql -u kalinza -p kalinza_local < database/migration_002_add_digital_division.sql

ALTER TABLE services
    MODIFY division ENUM('imprimerie','prod','digital') NOT NULL;

ALTER TABLE quote_requests
    MODIFY division ENUM('imprimerie','prod','digital') NOT NULL;

UPDATE site_settings SET setting_value = 'Kalinza Group — Imprimerie, Studio Photo/Vidéo & Digital' WHERE setting_key = 'meta_title_default';
UPDATE site_settings SET setting_value = 'Kalinza Group réunit Kalinza Imprimerie, Kalinza Prod et Kalinza Digital : impression, communication visuelle, studio photo, production vidéo, sites web et applications mobiles à Abidjan.' WHERE setting_key = 'meta_description_default';
