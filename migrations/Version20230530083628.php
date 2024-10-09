<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230530083628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE email_verification_code_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE email_verification_code (id INT NOT NULL, owner_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BD2ADC587E3C61F9 ON email_verification_code (owner_id)');
        $this->addSql('COMMENT ON COLUMN email_verification_code.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE email_verification_code ADD CONSTRAINT FK_BD2ADC587E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE email_verification_code_id_seq CASCADE');
        $this->addSql('ALTER TABLE email_verification_code DROP CONSTRAINT FK_BD2ADC587E3C61F9');
        $this->addSql('DROP TABLE email_verification_code');
    }
}
