<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240518130637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creating the adminrole table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE adminrole_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE adminrole (
            id INT DEFAULT 1 NOT NULL, 
            created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
            admin_id INT NOT NULL, 
            role_id INT NOT NULL, 
            PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN adminrole.admin_id IS \'The administrator id in the administrator table\'');
        $this->addSql('COMMENT ON COLUMN adminrole.role_id IS \'The role id in the role table\'');
        
        $this->addSql('CREATE INDEX idx_adminrole_created ON adminrole (created)');
        $this->addSql('CREATE INDEX idx_adminrole_admin_id ON adminrole (admin_id)');
        $this->addSql('CREATE INDEX idx_adminrole_role_id ON adminrole (role_id)');

        $this->addSql('ALTER TABLE administrator DROP roles');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE adminrole_id_seq CASCADE');
        $this->addSql('DROP TABLE adminrole');
        
        $this->addSql('ALTER TABLE administrator ADD roles TEXT DEFAULT \'[]\'');
        $this->addSql('COMMENT ON COLUMN administrator.roles IS \'a JSON text with array of role ids. Example: ["id":1,"id":2]\'');
    }
}
