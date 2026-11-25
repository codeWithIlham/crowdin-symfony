CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    roles JSON NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL
);

CREATE TABLE language (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE user_language (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    language_id INT NOT NULL,
    level ENUM('A1','A2','B1','B2','C1','C2') NOT NULL,

    UNIQUE KEY uniq_user_language (user_id, language_id),

    CONSTRAINT fk_userlanguage_user
        FOREIGN KEY (user_id) REFERENCES user(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_userlanguage_language
        FOREIGN KEY (language_id) REFERENCES language(id)
        ON DELETE CASCADE
);

CREATE TABLE project (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    owner_id INT NOT NULL,
    source_language_id INT NOT NULL,

    CONSTRAINT fk_project_owner
        FOREIGN KEY (owner_id) REFERENCES user(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_project_sourcelang
        FOREIGN KEY (source_language_id) REFERENCES language(id)
        ON DELETE RESTRICT
);

CREATE TABLE project_language (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    language_id INT NOT NULL,

    UNIQUE KEY uniq_project_language (project_id, language_id),

    CONSTRAINT fk_projectlanguage_project
        FOREIGN KEY (project_id) REFERENCES project(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_projectlanguage_language
        FOREIGN KEY (language_id) REFERENCES language(id)
        ON DELETE CASCADE
);

CREATE TABLE source (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    `key` VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,

    UNIQUE KEY uniq_source_key (project_id, `key`),

    CONSTRAINT fk_source_project
        FOREIGN KEY (project_id) REFERENCES project(id)
        ON DELETE CASCADE
);

CREATE TABLE translation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_id INT NOT NULL,
    language_id INT NOT NULL,
    translator_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL,

    UNIQUE KEY uniq_translation (source_id, language_id),

    CONSTRAINT fk_translation_source
        FOREIGN KEY (source_id) REFERENCES source(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_translation_language
        FOREIGN KEY (language_id) REFERENCES language(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_translation_translator
        FOREIGN KEY (translator_id) REFERENCES user(id)
        ON DELETE CASCADE
);
