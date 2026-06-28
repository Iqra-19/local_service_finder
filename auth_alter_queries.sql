-- ALTER queries for Google Authentication and Password Reset features
-- Run these queries on your existing local_service_finder database

ALTER TABLE users 
    MODIFY COLUMN password VARCHAR(255) DEFAULT NULL,
    ADD COLUMN google_id VARCHAR(255) DEFAULT NULL UNIQUE AFTER password,
    ADD COLUMN reset_token VARCHAR(64) DEFAULT NULL AFTER google_id,
    ADD COLUMN reset_expires DATETIME DEFAULT NULL AFTER reset_token,
    ADD INDEX idx_users_reset_token (reset_token);
