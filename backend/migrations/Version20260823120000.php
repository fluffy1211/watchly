<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260823120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add password reset token fields to utilisateur';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE utilisateur ADD reset_token VARCHAR(255) DEFAULT NULL, ADD reset_token_expires_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE utilisateur DROP reset_token, DROP reset_token_expires_at');
    }
}
