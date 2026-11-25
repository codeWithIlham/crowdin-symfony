<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add sourceLanguage to project, description to user, create user_language table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ADD source_language_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_project_source_lang FOREIGN KEY (source_language_id) REFERENCES language (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_project_source_lang ON project (source_language_id)');

        $this->addSql('ALTER TABLE `user` ADD description LONGTEXT DEFAULT NULL');

        $this->addSql('CREATE TABLE user_language (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            language_id INT NOT NULL,
            level VARCHAR(20) NOT NULL,
            INDEX IDX_UL_user (user_id),
            INDEX IDX_UL_lang (language_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE user_language ADD CONSTRAINT FK_UL_user FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_language ADD CONSTRAINT FK_UL_lang FOREIGN KEY (language_id) REFERENCES language (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_language DROP FOREIGN KEY FK_UL_user');
        $this->addSql('ALTER TABLE user_language DROP FOREIGN KEY FK_UL_lang');
        $this->addSql('DROP TABLE user_language');
        $this->addSql('ALTER TABLE `user` DROP COLUMN description');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_project_source_lang');
        $this->addSql('DROP INDEX IDX_project_source_lang ON project');
        $this->addSql('ALTER TABLE project DROP COLUMN source_language_id');
    }
}
