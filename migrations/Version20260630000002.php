<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260630000002 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE translation ADD status VARCHAR(20) NOT NULL DEFAULT 'pending'");
        $this->addSql("ALTER TABLE translation ADD review_comment LONGTEXT DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE translation DROP COLUMN status");
        $this->addSql("ALTER TABLE translation DROP COLUMN review_comment");
    }
}
