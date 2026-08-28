<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Collapse comment_report + review_report into a single single-table-inheritance
 * `report` table (discriminator `discr`, one nullable FK per target type).
 */
final class Version20260828120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Merge comment_report and review_report into a single report table (STI)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS comment_report');
        $this->addSql('DROP TABLE IF EXISTS review_report');

        $this->addSql(<<<'SQL'
            CREATE TABLE report (
                id INT AUTO_INCREMENT NOT NULL,
                reason LONGTEXT DEFAULT NULL,
                created_at DATETIME NOT NULL,
                reporter_id INT NOT NULL,
                comment_id INT DEFAULT NULL,
                review_id INT DEFAULT NULL,
                discr VARCHAR(20) NOT NULL,
                INDEX IDX_C42F7784E1CFE6F5 (reporter_id),
                INDEX IDX_C42F7784F8697D13 (comment_id),
                INDEX IDX_C42F77843E2E969B (review_id),
                UNIQUE INDEX uq_report_comment_reporter (comment_id, reporter_id),
                UNIQUE INDEX uq_report_review_reporter (review_id, reporter_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
            SQL);

        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_report_reporter FOREIGN KEY (reporter_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_report_comment FOREIGN KEY (comment_id) REFERENCES list_comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_report_review FOREIGN KEY (review_id) REFERENCES review (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE report');

        $this->addSql('CREATE TABLE comment_report (id INT AUTO_INCREMENT NOT NULL, reason LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, comment_id INT NOT NULL, reporter_id INT NOT NULL, INDEX IDX_E3C2F96F8697D13 (comment_id), INDEX IDX_E3C2F96E1CFE6F5 (reporter_id), UNIQUE INDEX uq_comment_report (comment_id, reporter_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE comment_report ADD CONSTRAINT FK_E3C2F96F8697D13 FOREIGN KEY (comment_id) REFERENCES list_comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment_report ADD CONSTRAINT FK_E3C2F96E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES utilisateur (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE review_report (id INT AUTO_INCREMENT NOT NULL, reason LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, review_id INT NOT NULL, reporter_id INT NOT NULL, INDEX IDX_4E5930443E2E969B (review_id), INDEX IDX_4E593044E1CFE6F5 (reporter_id), UNIQUE INDEX uq_review_report (review_id, reporter_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE review_report ADD CONSTRAINT FK_4E5930443E2E969B FOREIGN KEY (review_id) REFERENCES review (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review_report ADD CONSTRAINT FK_4E593044E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
    }
}
