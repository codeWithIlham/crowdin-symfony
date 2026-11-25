<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701000001 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE bug_report (
            id INT AUTO_INCREMENT NOT NULL,
            reporter_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            page VARCHAR(255) DEFAULT NULL,
            description LONGTEXT NOT NULL,
            is_resolved TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            INDEX IDX_bug_reporter (reporter_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("ALTER TABLE bug_report ADD CONSTRAINT FK_bug_reporter FOREIGN KEY (reporter_id) REFERENCES `user` (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE bug_report DROP FOREIGN KEY FK_bug_reporter");
        $this->addSql("DROP TABLE bug_report");
    }
}
