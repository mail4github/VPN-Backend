<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240411142236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction ADD server_id INT DEFAULT 0');
        $this->addSql('CREATE INDEX idx_transaction_server_id ON transaction (server_id)');
        $this->addSql('COMMENT ON COLUMN transaction.server_id IS \'ID of VPN server in the vpn_server table\'');

        $this->addSql('ALTER TABLE vpn_connection ADD total_traffic DOUBLE PRECISION DEFAULT \'0\'');
        $this->addSql('CREATE INDEX idx_vpn_connection_total_traffic ON vpn_connection (total_traffic)');
        $this->addSql('COMMENT ON COLUMN vpn_connection.total_traffic IS \'Total traffic that has been sent and received during the connection\'');
        $this->addSql('COMMENT ON COLUMN vpn_connection.user_id IS \'User who connected to this server. Id in the User table\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction DROP server_id');
        $this->addSql('ALTER TABLE vpn_connection DROP total_traffic');
    }
}
