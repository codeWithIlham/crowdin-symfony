<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701000002 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE project_user (
            project_id INT NOT NULL,
            user_id INT NOT NULL,
            PRIMARY KEY(project_id, user_id),
            INDEX IDX_PU_project (project_id),
            INDEX IDX_PU_user (user_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("ALTER TABLE project_user
            ADD CONSTRAINT FK_PU_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE,
            ADD CONSTRAINT FK_PU_user FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE project_user DROP FOREIGN KEY FK_PU_project");
        $this->addSql("ALTER TABLE project_user DROP FOREIGN KEY FK_PU_user");
        $this->addSql("DROP TABLE project_user");
    }
}
