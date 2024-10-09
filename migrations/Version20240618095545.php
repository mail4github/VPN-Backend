<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240618095545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vpn_connection ADD client_config TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE vpn_connection ADD client_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE vpn_server ADD is_ready_to_use BOOLEAN DEFAULT false NOT NULL');

        $this->addSql('COMMENT ON COLUMN vpn_connection.client_config IS \'Encoded client configuration file\'');
        $this->addSql('COMMENT ON COLUMN vpn_connection.client_name IS \'VPN Client Name\'');
        $this->addSql('COMMENT ON COLUMN vpn_server.is_ready_to_use IS \'Flag indicates that server ready to use or not\'');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vpn_server DROP is_ready_to_use');
        $this->addSql('ALTER TABLE vpn_connection DROP client_config');
        $this->addSql('ALTER TABLE vpn_connection DROP client_name');
    }
}
