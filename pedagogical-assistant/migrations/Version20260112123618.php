<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260112123618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE exercise (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, consigne CLOB NOT NULL, correction CLOB DEFAULT NULL, difficulte VARCHAR(50) NOT NULL, competences_ciblees CLOB NOT NULL, created_at DATETIME NOT NULL, session_id INTEGER NOT NULL, CONSTRAINT FK_AEDAD51C613FECDF FOREIGN KEY (session_id) REFERENCES session (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_AEDAD51C613FECDF ON exercise (session_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE exercise');
    }
}
