<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240422092733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nimbus ADD owner_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN nimbus.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE nimbus ADD CONSTRAINT FK_E4547107E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E4547107E3C61F9 ON nimbus (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE nimbus DROP CONSTRAINT FK_E4547107E3C61F9');
        $this->addSql('DROP INDEX IDX_E4547107E3C61F9');
        $this->addSql('ALTER TABLE nimbus DROP owner_id');
    }
}
