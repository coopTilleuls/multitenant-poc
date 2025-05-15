<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250515125249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE books (id UUID NOT NULL, owner_id UUID DEFAULT NULL, nom VARCHAR(180) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4A1B2A926C6E55B5 ON books (nom)');
        $this->addSql('CREATE INDEX IDX_4A1B2A927E3C61F9 ON books (owner_id)');
        $this->addSql('COMMENT ON COLUMN books.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN books.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE users (id UUID NOT NULL, owner_id UUID DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, sql_db_name VARCHAR(255) DEFAULT NULL, sql_user_name VARCHAR(255) DEFAULT NULL, db_created BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE INDEX IDX_1483A5E97E3C61F9 ON users (owner_id)');
        $this->addSql('COMMENT ON COLUMN users.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN users.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE books ADD CONSTRAINT FK_4A1B2A927E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E97E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE books DROP CONSTRAINT FK_4A1B2A927E3C61F9');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E97E3C61F9');
        $this->addSql('DROP TABLE books');
        $this->addSql('DROP TABLE users');
    }
}
