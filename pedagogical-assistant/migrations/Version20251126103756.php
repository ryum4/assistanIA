<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251126103756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE session (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, objectifs CLOB NOT NULL, contenus CLOB NOT NULL, activites CLOB NOT NULL, done BOOLEAN NOT NULL, notes_reelles CLOB DEFAULT NULL, course_plan_id INTEGER NOT NULL, CONSTRAINT FK_D044D5D4E05A0777 FOREIGN KEY (course_plan_id) REFERENCES course_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_D044D5D4E05A0777 ON session (course_plan_id)');
        $this->addSql('CREATE TABLE syllabus (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, filename VARCHAR(255) DEFAULT NULL, texte CLOB DEFAULT NULL)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__course_plan AS SELECT id FROM course_plan');
        $this->addSql('DROP TABLE course_plan');
        $this->addSql('CREATE TABLE course_plan (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, plan_general CLOB DEFAULT NULL, evaluation CLOB NOT NULL, syllabus_id INTEGER NOT NULL, CONSTRAINT FK_15F8867B824D79E7 FOREIGN KEY (syllabus_id) REFERENCES syllabus (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO course_plan (id) SELECT id FROM __temp__course_plan');
        $this->addSql('DROP TABLE __temp__course_plan');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_15F8867B824D79E7 ON course_plan (syllabus_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE syllabus');
        $this->addSql('CREATE TEMPORARY TABLE __temp__course_plan AS SELECT id FROM course_plan');
        $this->addSql('DROP TABLE course_plan');
        $this->addSql('CREATE TABLE course_plan (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL)');
        $this->addSql('INSERT INTO course_plan (id) SELECT id FROM __temp__course_plan');
        $this->addSql('DROP TABLE __temp__course_plan');
    }
}
