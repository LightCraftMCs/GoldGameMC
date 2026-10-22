-- GoldGameMC schema: utf8mb4, InnoDB, unique identities, order records, rate limiting, audit trail.
CREATE DATABASE IF NOT EXISTS goldgame CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE goldgame;
CREATE TABLE IF NOT EXISTS users (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 email VARCHAR(254) NOT NULL,
 username VARCHAR(24) NOT NULL,
 gamer_username VARCHAR(16) NOT NULL,
 password_hash VARCHAR(255) NOT NULL,
 email_verified_at DATETIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_users_email(email), UNIQUE KEY uq_users_username(username), UNIQUE KEY uq_users_gamer_username(gamer_username),
 INDEX idx_users_created_at(created_at)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS rank_orders (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 public_reference CHAR(20) NOT NULL,
 user_id BIGINT UNSIGNED NULL,
 rank_name ENUM('Plus','Vip','MVP','Premium','Sponsor') NOT NULL,
 price_toman INT UNSIGNED NOT NULL,
 gamer_username VARCHAR(16) NOT NULL,
 status ENUM('pending','awaiting_payment','paid','fulfilled','cancelled','refunded') NOT NULL DEFAULT 'pending',
 support_reference VARCHAR(80) NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_rank_orders_public_reference(public_reference),
 INDEX idx_orders_user_created(user_id,created_at), INDEX idx_orders_status_created(status,created_at),
 CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS auth_attempts (
 attempt_key CHAR(64) NOT NULL PRIMARY KEY,
 attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 last_attempt_at DATETIME NOT NULL,
 INDEX idx_auth_attempts_time(last_attempt_at)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS email_verification_tokens (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 token_hash CHAR(64) NOT NULL,
 expires_at DATETIME NOT NULL,
 used_at DATETIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_email_token_hash(token_hash), INDEX idx_email_tokens_user(user_id),
 CONSTRAINT fk_email_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS password_reset_tokens (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 token_hash CHAR(64) NOT NULL,
 expires_at DATETIME NOT NULL,
 used_at DATETIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_password_reset_hash(token_hash), INDEX idx_reset_user(user_id),
 CONSTRAINT fk_reset_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS audit_log (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 event_name VARCHAR(60) NOT NULL,
 meta_json JSON NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_audit_event_created(event_name,created_at), INDEX idx_audit_user_created(user_id,created_at),
 CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
