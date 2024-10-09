<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240414103607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vpn_server ALTER modified TYPE TIMESTAMP(0) WITHOUT TIME ZONE USING DATE(NOW()) + modified');
        $this->addSql('CREATE INDEX idx_vpn_server_connection_quality ON vpn_server (connection_quality)');
        $this->addSql('CREATE INDEX idx_vpn_server_created ON vpn_server (created)');
        $this->addSql('CREATE INDEX idx_vpn_server_price ON vpn_server (price)');
        $this->addSql('CREATE INDEX idx_vpn_server_user_name ON vpn_server (user_name)');
        $this->addSql('CREATE INDEX idx_vpn_server_country ON vpn_server (country)');
        $this->addSql('CREATE INDEX idx_vpn_server_ip ON vpn_server (ip)');
        $this->addSql('CREATE INDEX idx_vpn_server_protocol ON vpn_server (protocol)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vpn_server ALTER modified TYPE TIME(0) WITHOUT TIME ZONE');
    }
}
