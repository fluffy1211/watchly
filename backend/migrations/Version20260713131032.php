<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260713131032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment_report (id INT AUTO_INCREMENT NOT NULL, reason LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, comment_id INT NOT NULL, reporter_id INT NOT NULL, INDEX IDX_E3C2F96F8697D13 (comment_id), INDEX IDX_E3C2F96E1CFE6F5 (reporter_id), UNIQUE INDEX uq_comment_report (comment_id, reporter_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE comment_report ADD CONSTRAINT FK_E3C2F96F8697D13 FOREIGN KEY (comment_id) REFERENCES list_comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment_report ADD CONSTRAINT FK_E3C2F96E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment_report DROP FOREIGN KEY FK_E3C2F96F8697D13');
        $this->addSql('ALTER TABLE comment_report DROP FOREIGN KEY FK_E3C2F96E1CFE6F5');
        $this->addSql('DROP TABLE comment_report');
    }
}
