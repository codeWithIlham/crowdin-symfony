<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260625114544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE translation ADD content LONGTEXT NOT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, ADD language_id INT NOT NULL, ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE translation ADD CONSTRAINT FK_B469456F82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE translation ADD CONSTRAINT FK_B469456FA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_B469456F82F1BAF4 ON translation (language_id)');
        $this->addSql('CREATE INDEX IDX_B469456FA76ED395 ON translation (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE translation DROP FOREIGN KEY FK_B469456F82F1BAF4');
        $this->addSql('ALTER TABLE translation DROP FOREIGN KEY FK_B469456FA76ED395');
        $this->addSql('DROP INDEX IDX_B469456F82F1BAF4 ON translation');
        $this->addSql('DROP INDEX IDX_B469456FA76ED395 ON translation');
        $this->addSql('ALTER TABLE translation DROP content, DROP created_at, DROP updated_at, DROP language_id, DROP user_id');
    }
}
