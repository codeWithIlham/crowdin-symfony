<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260628000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create language, project, project_language and translation tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE language (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2FB3D0EE7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_language (project_id INT NOT NULL, language_id INT NOT NULL, INDEX IDX_B3988CB9166D1F9C (project_id), INDEX IDX_B3988CB982F1BAF4 (language_id), PRIMARY KEY(project_id, language_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE translation (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, language_id INT NOT NULL, translator_id INT DEFAULT NULL, source_key VARCHAR(255) NOT NULL, source_text LONGTEXT NOT NULL, translated_text LONGTEXT DEFAULT NULL, is_approved TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B469456F166D1F9C (project_id), INDEX IDX_B469456F82F1BAF4 (language_id), INDEX IDX_B469456F9817C70D (translator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE project_language ADD CONSTRAINT FK_B3988CB9166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_language ADD CONSTRAINT FK_B3988CB982F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE translation ADD CONSTRAINT FK_B469456F166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE translation ADD CONSTRAINT FK_B469456F82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE translation ADD CONSTRAINT FK_B469456F9817C70D FOREIGN KEY (translator_id) REFERENCES `user` (id)');
        $this->addSql("INSERT INTO language (name, code) VALUES ('Français', 'fr'), ('English', 'en'), ('Español', 'es'), ('Deutsch', 'de'), ('Italiano', 'it'), ('Português', 'pt'), ('العربية', 'ar'), ('中文', 'zh'), ('日本語', 'ja'), ('한국어', 'ko')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE7E3C61F9');
        $this->addSql('ALTER TABLE project_language DROP FOREIGN KEY FK_B3988CB9166D1F9C');
        $this->addSql('ALTER TABLE project_language DROP FOREIGN KEY FK_B3988CB982F1BAF4');
        $this->addSql('ALTER TABLE translation DROP FOREIGN KEY FK_B469456F166D1F9C');
        $this->addSql('ALTER TABLE translation DROP FOREIGN KEY FK_B469456F82F1BAF4');
        $this->addSql('ALTER TABLE translation DROP FOREIGN KEY FK_B469456F9817C70D');
        $this->addSql('DROP TABLE translation');
        $this->addSql('DROP TABLE project_language');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE language');
    }
}
