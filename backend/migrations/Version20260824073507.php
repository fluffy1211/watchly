<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260824073507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE review_report (id INT AUTO_INCREMENT NOT NULL, reason LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, review_id INT NOT NULL, reporter_id INT NOT NULL, INDEX IDX_4E5930443E2E969B (review_id), INDEX IDX_4E593044E1CFE6F5 (reporter_id), UNIQUE INDEX uq_review_report (review_id, reporter_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE review_report ADD CONSTRAINT FK_4E5930443E2E969B FOREIGN KEY (review_id) REFERENCES review (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review_report ADD CONSTRAINT FK_4E593044E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review_report DROP FOREIGN KEY FK_4E5930443E2E969B');
        $this->addSql('ALTER TABLE review_report DROP FOREIGN KEY FK_4E593044E1CFE6F5');
        $this->addSql('DROP TABLE review_report');
    }
}
