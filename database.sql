CREATE DATABASE IF NOT EXISTS `hivephp` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `hivephp`;

CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(30)    NOT NULL,
    `surname`    VARCHAR(30)    NOT NULL,
    `avatar`     VARCHAR(255)   DEFAULT NULL,
    `status`     VARCHAR(120)   DEFAULT NULL,
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

CREATE TABLE IF NOT EXISTS `albums` (
    `id`           INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`      INT NOT NULL,
    `title`        VARCHAR(255) NOT NULL,
    `description`  TEXT DEFAULT NULL,
    `sort_order`   INT NOT NULL DEFAULT 0,
    `is_protected` TINYINT NOT NULL DEFAULT 0,
    `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_album_user_sort` (`user_id`, `sort_order`),
    CONSTRAINT `fk_album_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `photos` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `album_id`   INT NOT NULL,
    `user_id`    INT NOT NULL,
    `path`       VARCHAR(255) NOT NULL,
    `thumb`      VARCHAR(255) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `avatar_url` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_photo_album` (`album_id`),
    INDEX `idx_photo_album_sort` (`album_id`, `sort_order`),
    CONSTRAINT `fk_photo_album` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_photo_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `friends` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT NOT NULL COMMENT 'initiator / requester',
    `friend_id`   INT NOT NULL COMMENT 'recipient / target',
    `status`      ENUM('pending','accepted') NOT NULL DEFAULT 'pending',
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_friendship` (`user_id`, `friend_id`),
    INDEX `idx_friends_friend_user` (`friend_id`, `status`),
    INDEX `idx_friends_user_status` (`user_id`, `status`),
    CONSTRAINT `fk_friend_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_friend_other` FOREIGN KEY (`friend_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
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