<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240324160302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE device_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE device (id INT DEFAULT 1 NOT NULL, user_id INT NOT NULL, ip TEXT NOT NULL, active BOOLEAN DEFAULT false, name TEXT NOT NULL, fingerprint TEXT NOT NULL, country TEXT NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, modified TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, connected TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX device_uniq_user ON device (user_id, ip, fingerprint)');
        $this->addSql('COMMENT ON COLUMN device.user_id IS \'ID in the User table\'');
        $this->addSql('COMMENT ON COLUMN device.ip IS \'IP address of the user device\'');
        $this->addSql('COMMENT ON COLUMN device.active IS \'If true then this device is active device\'');
        $this->addSql('COMMENT ON COLUMN device.fingerprint IS \'Fingerprint of the user device\'');
        $this->addSql('COMMENT ON COLUMN device.country IS \'Country of the user device\'');
		$this->addSql('COMMENT ON COLUMN device.connected IS \'date and time of last connection\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE device_id_seq CASCADE');
        $this->addSql('DROP TABLE device');
    }
}
