<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240504084311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction ALTER tr_type TYPE VARCHAR(16)');
        $this->addSql('COMMENT ON COLUMN transaction.user_id IS \'ID of the owner of this transaction in the User table\'');
        $this->addSql('COMMENT ON COLUMN transaction.server_id IS \'ID of VPN server in the vpn_server table\'');
        $this->addSql('ALTER TABLE vpn_connection ADD connection_type VARCHAR(32) DEFAULT \'traffic\'');
        $this->addSql('COMMENT ON COLUMN vpn_connection.connection_type IS \'Type of connection like: test_traffic, test_period, traffic, period\'');
        $this->addSql('COMMENT ON COLUMN vpn_connection.user_id IS \'User who has connected to this server. Id in the User table\'');
        $this->addSql('CREATE INDEX idx_vpn_connection_connection_type ON vpn_connection (connection_type)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction ALTER tr_type TYPE VARCHAR(3)');
        $this->addSql('ALTER TABLE vpn_connection DROP connection_type');
    }
}
