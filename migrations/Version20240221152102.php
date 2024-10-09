<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240221152102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE IF NOT EXISTS vpn_server (id INT NOT NULL, country TEXT NOT NULL, ip TEXT DEFAULT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, modified TIME(0) WITHOUT TIME ZONE NOT NULL, created_by INT DEFAULT NULL, for_free BOOLEAN DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, protocol TEXT DEFAULT \'WireGuard\', user_name TEXT DEFAULT NULL, residential_ip BOOLEAN DEFAULT NULL, connection_quality INT DEFAULT NULL, service_commission DOUBLE PRECISION DEFAULT NULL, maximum_active_connections INT DEFAULT NULL, test_package_until_traffic_volume DOUBLE PRECISION DEFAULT NULL, test_package_until_traffic_price DOUBLE PRECISION DEFAULT NULL, test_package_for_period_time DOUBLE PRECISION DEFAULT NULL, test_package_for_period_price DOUBLE PRECISION DEFAULT NULL, traffic_vs_period BOOLEAN DEFAULT true, password TEXT NOT NULL, test_packages TEXT DEFAULT \'[]\', paid_packages TEXT DEFAULT \'[]\', PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN vpn_server.created_by IS \'User id of a person who added this server\'');
        $this->addSql('COMMENT ON COLUMN vpn_server.for_free IS \'Is it possible to connect to this server for free\'');
        $this->addSql('COMMENT ON COLUMN vpn_server.protocol IS \'Possible values: WireGuard, OpenVPN (UDP), OpenVPN (TCP)\'');
        $this->addSql('COMMENT ON COLUMN vpn_server.residential_ip IS \'Is this server located at a residential area\'');
        $this->addSql('COMMENT ON COLUMN vpn_server.connection_quality IS \'0 - is best quality, 1 - fair quality, 2 - poor quality\'');
        $this->addSql('COMMENT ON COLUMN vpn_server.service_commission IS \'A commission for this service in percents\'');
        $this->addSql('COMMENT ON COLUMN vpn_server.maximum_active_connections IS \'Number of maximum active connections. If zero then no limits\'');
        $this->addSql('COMMENT ON COLUMN vpn_server.test_package_until_traffic_volume IS \'The volume of traffic for the test package\'');
        $this->addSql('COMMENT ON COLUMN vpn_server.test_package_until_traffic_price IS \'The price of test package with limited traffic\'');
        $this->addSql('COMMENT ON COLUMN vpn_server.test_package_for_period_time IS \'The duration of the test package which is active during a period of time\'');
        $this->addSql('COMMENT ON COLUMN vpn_server.test_package_for_period_price IS \'The price of limited time test package\'');
        $this->addSql('COMMENT ON COLUMN vpn_server.traffic_vs_period IS \'If true then the traffic is active. If false then period of time\'');
        $this->addSql('COMMENT ON COLUMN vpn_server.test_packages IS \'a JSON text with array of test packages\'');
        $this->addSql('COMMENT ON COLUMN vpn_server.paid_packages IS \'a JSON text with array of paid packages\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE vpn_server');
    }
}
