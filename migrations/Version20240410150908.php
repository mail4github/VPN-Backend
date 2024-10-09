<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240410150908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE vpn_connection_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE vpn_connection (
            id INT DEFAULT 1 NOT NULL, 
            user_id INT NOT NULL, 
            ip VARCHAR(20) NOT NULL, 
            country VARCHAR(3) DEFAULT \'\', 
            created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
            modified TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
            server_id INT NOT NULL, 
            duration DOUBLE PRECISION DEFAULT \'0\',
            description VARCHAR(255) DEFAULT \'\',
            protocol VARCHAR(64) DEFAULT \'WireGuard\', 
            PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN vpn_connection.user_id IS \'User who connected to this server. Id in the User table\'');
        $this->addSql('COMMENT ON COLUMN vpn_connection.ip IS \'IP address of the connected user\'');
        $this->addSql('COMMENT ON COLUMN vpn_connection.country IS \'Country of the connected user\'');
        $this->addSql('COMMENT ON COLUMN vpn_connection.server_id IS \'Id of VPN server\'');
        $this->addSql('COMMENT ON COLUMN vpn_connection.duration IS \'The time that user spent on the server (in seconds, probably)\'');
        $this->addSql('COMMENT ON COLUMN vpn_connection.protocol IS \'Possible values: WireGuard, OpenVPN (UDP), OpenVPN (TCP)\'');
        $this->addSql('CREATE INDEX IDX_vpn_connection_user_id ON vpn_connection (user_id)');
        $this->addSql('CREATE INDEX IDX_vpn_connection_ip ON vpn_connection (ip)');
        $this->addSql('CREATE INDEX IDX_vpn_connection_country ON vpn_connection (country)');
		$this->addSql('CREATE INDEX IDX_vpn_connection_created ON vpn_connection (created)');
		$this->addSql('CREATE INDEX IDX_vpn_connection_modified ON vpn_connection (modified)');
        $this->addSql('CREATE INDEX IDX_vpn_connection_server_id ON vpn_connection (server_id)');
        $this->addSql('CREATE INDEX IDX_vpn_connection_duration ON vpn_connection (duration)');
        $this->addSql('CREATE INDEX IDX_vpn_connection_protocol ON vpn_connection (protocol)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE vpn_connection_id_seq CASCADE');
        $this->addSql('DROP TABLE vpn_connection');
    }
}
