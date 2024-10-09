<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240220190904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ALTER is_email_verified DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" ALTER is_two_factor_auth_enabled DROP DEFAULT');
        $this->addSql('ALTER TABLE vpn_server ADD password text DEFAULT \'\' NOT NULL');
        $this->addSql('COMMENT ON COLUMN vpn_server.test_package_for_period_price IS \'The price of limited time test package\'');
        $this->addSql('COMMENT ON COLUMN vpn_server.traffic_vs_period IS \'If true then the traffic is active. If false then period of time\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE vpn_server DROP password');
        $this->addSql('COMMENT ON COLUMN vpn_server.test_package_for_period_price IS NULL');
        $this->addSql('COMMENT ON COLUMN vpn_server.traffic_vs_period IS NULL');
        $this->addSql('ALTER TABLE "user" ALTER is_email_verified SET DEFAULT false');
        $this->addSql('ALTER TABLE "user" ALTER is_two_factor_auth_enabled SET DEFAULT false');
    }
}
