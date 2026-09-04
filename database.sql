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
    `last_seen_at` TIMESTAMP    NULL DEFAULT NULL,
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

CREATE TABLE IF NOT EXISTS `notifications` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT NOT NULL COMMENT 'recipient',
    `type`       VARCHAR(40)   NOT NULL,
    `section`    VARCHAR(30)   NOT NULL DEFAULT 'general',
    `actor_id`   INT DEFAULT NULL COMMENT 'user who caused the notification',
    `data`       TEXT DEFAULT NULL COMMENT 'extra JSON payload',
    `link`       VARCHAR(255)  DEFAULT NULL,
    `is_read`    TINYINT       NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_notif_user_read`    (`user_id`, `is_read`, `id`),
    INDEX `idx_notif_user_created` (`user_id`, `created_at`),
    CONSTRAINT `fk_notif_user`  FOREIGN KEY (`user_id`)  REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_notif_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `conversations` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `user_low`        INT  NOT NULL COMMENT 'min(user_a, user_b)',
    `user_high`       INT  NOT NULL COMMENT 'max(user_a, user_b)',
    `last_message_id` INT DEFAULT NULL,
    `last_message_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_conversation` (`user_low`, `user_high`),
    INDEX `idx_conv_user_low`  (`user_low`),
    INDEX `idx_conv_user_high` (`user_high`),
    CONSTRAINT `fk_conv_low`  FOREIGN KEY (`user_low`)  REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_conv_high` FOREIGN KEY (`user_high`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `messages` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` INT          NOT NULL,
    `sender_id`       INT          NOT NULL,
    `recipient_id`    INT          NOT NULL,
    `body`            TEXT         NOT NULL,
    `is_read`         TINYINT      NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_msg_conv`            (`conversation_id`, `id`),
    INDEX `idx_msg_recipient_unread` (`recipient_id`, `is_read`),
    CONSTRAINT `fk_msg_conv`     FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_msg_sender`    FOREIGN KEY (`sender_id`)    REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_msg_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
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

CREATE TABLE IF NOT EXISTS `documents` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT            NOT NULL,
    `name`       VARCHAR(255)   NOT NULL,
    `type`       VARCHAR(100)   NOT NULL DEFAULT 'application/octet-stream',
    `size`       INT UNSIGNED   NOT NULL DEFAULT 0,
    `path`       VARCHAR(500)   NOT NULL,
    `created_at` TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_doc_user` (`user_id`),
    INDEX `idx_doc_user_created` (`user_id`, `created_at`),
    CONSTRAINT `fk_doc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;