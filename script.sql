CREATE DATABASE IF NOT EXISTS `philomathos`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE `philomathos`;


CREATE TABLE IF NOT EXISTS `users`
(
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    `username`   VARCHAR(50)                             NOT NULL,
    `email`      VARCHAR(100)                            NOT NULL UNIQUE,
    `role`       ENUM ('admin','user')                   NOT NULL DEFAULT 'user',
    `password`   VARCHAR(255)                            NOT NULL, -- mot de passe haché
    `created_at` TIMESTAMP
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `corpora`
(
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    `title`       VARCHAR(255)                            NOT NULL,
    `description` TEXT,
    `language`    VARCHAR(100)                            NOT NULL,
    `created_by`  INT UNSIGNED                            NOT NULL,
    `created_at`  TIMESTAMP,

    CONSTRAINT `fk_corpora_created_by`
        FOREIGN KEY (`created_by`)
            REFERENCES `users` (`id`)
            ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `files`
(
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    `corpus_id`   INT UNSIGNED                            NOT NULL,
    `original`    VARCHAR(255)                            NOT NULL,
    `processed`   VARCHAR(255)                            NOT NULL,
    `text`        TEXT                                    NOT NULL,
    `uploaded_by` INT UNSIGNED                            NOT NULL,
    `created_at`  TIMESTAMP,

    CONSTRAINT `fk_files_corpus`
        FOREIGN KEY (`corpus_id`)
            REFERENCES `corpora` (`id`)
            ON DELETE CASCADE,

    CONSTRAINT `fk_files_user`
        FOREIGN KEY (`uploaded_by`)
            REFERENCES `users` (`id`)
            ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `corrections`
(
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    `image_id`      INT UNSIGNED NOT NULL,
    `proposed_by`   INT UNSIGNED NOT NULL,
    `correction`    TEXT,
    `comment`       TEXT,
    `created_at`    TIMESTAMP,
    `accepted`      BOOLEAN,
    `accepted_by`   INT UNSIGNED NOT NULL,
    `treated_at`    DATETIME,

    CONSTRAINT `fk_corrections_image`
        FOREIGN KEY (`image_id`)
            REFERENCES `files` (`id`)
            ON DELETE CASCADE,

    CONSTRAINT `fk_corrections_user`
        FOREIGN KEY (`proposed_by`)
            REFERENCES `users` (`id`)
            ON DELETE CASCADE,

    CONSTRAINT `fk_corrections_master`
        FOREIGN KEY (`accepted_by`)
            REFERENCES `users` (`id`)
            ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `settings`
(
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    `site_name`        VARCHAR(255)                            NOT NULL,
    `default_language` VARCHAR(100)                            NOT NULL,
    `max_upload_size`  INT UNSIGNED                            NOT NULL, -- en Mo
    `updated_at`       TIMESTAMP
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

INSERT INTO settings (site_name, default_language, max_upload_size)
VALUES ('Philomathos', 'en', 500);