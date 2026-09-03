CREATE DATABASE IF NOT EXISTS `lovly` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `lovly`;

CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(30)    NOT NULL,
    `surname`    VARCHAR(30)    NOT NULL,
    `sex`        ENUM('male','female','other') NOT NULL,
    `email`      VARCHAR(100)   NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `city`       VARCHAR(50)    NOT NULL,
    `country`    VARCHAR(50)    NOT NULL,
    `day`        TINYINT        NOT NULL,
    `month`      TINYINT        NOT NULL,
    `year`       SMALLINT       NOT NULL,
    `about`      TEXT           DEFAULT NULL,
    `interests`  TEXT           DEFAULT NULL,
    `favorite_films` TEXT       DEFAULT NULL,
    `created_at` TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_remember_tokens` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT            NOT NULL,
    `token_hash` VARCHAR(64)    NOT NULL,
    `user_agent` VARCHAR(255)   NOT NULL,
    `ip`         VARCHAR(45)    NOT NULL,
    `expires_at` DATETIME       NOT NULL,
    `created_at` TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_token_hash` (`token_hash`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_expires_at` (`expires_at`),
    CONSTRAINT `fk_token_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;