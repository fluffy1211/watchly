<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260713124951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE list_comment (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, list_id INT NOT NULL, author_id INT NOT NULL, INDEX IDX_483B8F613DAE168B (list_id), INDEX IDX_483B8F61F675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE list_film (id INT AUTO_INCREMENT NOT NULL, added_at DATETIME NOT NULL, list_id INT NOT NULL, film_id INT NOT NULL, INDEX IDX_C78A61CB3DAE168B (list_id), INDEX IDX_C78A61CB567F5183 (film_id), UNIQUE INDEX uq_list_film (list_id, film_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE movie_list (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, visibility ENUM(\'PUBLIC\', \'PRIVATE\') NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, owner_id INT NOT NULL, INDEX IDX_B7AED9157E3C61F9 (owner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE list_comment ADD CONSTRAINT FK_483B8F613DAE168B FOREIGN KEY (list_id) REFERENCES movie_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE list_comment ADD CONSTRAINT FK_483B8F61F675F31B FOREIGN KEY (author_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE list_film ADD CONSTRAINT FK_C78A61CB3DAE168B FOREIGN KEY (list_id) REFERENCES movie_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE list_film ADD CONSTRAINT FK_C78A61CB567F5183 FOREIGN KEY (film_id) REFERENCES film (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE movie_list ADD CONSTRAINT FK_B7AED9157E3C61F9 FOREIGN KEY (owner_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_collection CHANGE status status ENUM(\'WATCHLIST\', \'WATCHED\') NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE list_comment DROP FOREIGN KEY FK_483B8F613DAE168B');
        $this->addSql('ALTER TABLE list_comment DROP FOREIGN KEY FK_483B8F61F675F31B');
        $this->addSql('ALTER TABLE list_film DROP FOREIGN KEY FK_C78A61CB3DAE168B');
        $this->addSql('ALTER TABLE list_film DROP FOREIGN KEY FK_C78A61CB567F5183');
        $this->addSql('ALTER TABLE movie_list DROP FOREIGN KEY FK_B7AED9157E3C61F9');
        $this->addSql('DROP TABLE list_comment');
        $this->addSql('DROP TABLE list_film');
        $this->addSql('DROP TABLE movie_list');
        $this->addSql('ALTER TABLE user_collection CHANGE status status ENUM(\'WATCHLIST\', \'WATCHED\') NOT NULL');
    }
}
