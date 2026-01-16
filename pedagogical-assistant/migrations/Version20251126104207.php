<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251126104207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__syllabus AS SELECT id, filename, texte FROM syllabus');
        $this->addSql('DROP TABLE syllabus');
        $this->addSql('CREATE TABLE syllabus (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, filename VARCHAR(255) DEFAULT NULL, raw_text CLOB DEFAULT NULL, extracted_competences CLOB DEFAULT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO syllabus (id, filename, raw_text) SELECT id, filename, texte FROM __temp__syllabus');
        $this->addSql('DROP TABLE __temp__syllabus');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__syllabus AS SELECT id, filename FROM syllabus');
        $this->addSql('DROP TABLE syllabus');
        $this->addSql('CREATE TABLE syllabus (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, filename VARCHAR(255) DEFAULT NULL, texte CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO syllabus (id, filename) SELECT id, filename FROM __temp__syllabus');
        $this->addSql('DROP TABLE __temp__syllabus');
    }
}
