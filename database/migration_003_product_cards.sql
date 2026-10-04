-- Migration 003 — Cartes produit : quantité minimum, délai de livraison, image
-- À exécuter uniquement si vous avez déjà importé schema.sql avant cette mise à jour.
-- Exemple : mysql -u kalinza -p kalinza_local < database/migration_003_product_cards.sql

ALTER TABLE services
    ADD COLUMN min_quantity VARCHAR(60) NULL AFTER price_note,
    ADD COLUMN delivery_time VARCHAR(100) NULL AFTER min_quantity;

-- La colonne cover_image existe déjà dans le schéma initial (utilisée pour l'image de la carte produit).
