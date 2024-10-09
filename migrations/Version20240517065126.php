<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240517065126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creating the administrator table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE administrator_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE administrator (
            id INT DEFAULT 1 NOT NULL, 
            created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
            modified TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
            last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
            login VARCHAR(64) NOT NULL, 
            description VARCHAR(256) DEFAULT \'\', 
            pgp_public_key VARCHAR(10240) NOT NULL, 
            superadmin BOOLEAN DEFAULT false, 
            blocked BOOLEAN DEFAULT false, 
            roles TEXT DEFAULT \'[]\', 
            PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN administrator.login IS \'Login name of this administrator\'');
        $this->addSql('COMMENT ON COLUMN administrator.description IS \'Description of this administrator\'');
        $this->addSql('COMMENT ON COLUMN administrator.pgp_public_key IS \'A string with PHP public key of this administrator. Is using to login\'');
        $this->addSql('COMMENT ON COLUMN administrator.superadmin IS \'If this value is true then this admin can manage other admins\'');
        $this->addSql('COMMENT ON COLUMN administrator.blocked IS \'If this value is true then this admin is disabled\'');
        $this->addSql('COMMENT ON COLUMN administrator.roles IS \'a JSON text with array of role ids. Example: ["id":1,"id":2]\'');
        
        $this->addSql('CREATE INDEX idx_administrator_created ON administrator (created)');
        $this->addSql('CREATE INDEX idx_administrator_last_login ON administrator (last_login)');
        $this->addSql('CREATE INDEX idx_administrator_login ON administrator (login)');
        $this->addSql('CREATE INDEX idx_administrator_superadmin ON administrator (superadmin)');
        $this->addSql('CREATE INDEX idx_administrator_blocked ON administrator (blocked)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE administrator_id_seq CASCADE');
        $this->addSql('DROP TABLE administrator');
    }
}
