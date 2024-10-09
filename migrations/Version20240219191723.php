<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240219191723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE IF NOT EXISTS public.vpn_server
		(
			id integer NOT NULL,
			country text COLLATE pg_catalog."default" NOT NULL,
			ip text COLLATE pg_catalog."default",
			created timestamp without time zone NOT NULL,
			modified time(0) without time zone NOT NULL,
			created_by integer,
			for_free boolean,
			price double precision,
			protocol text COLLATE pg_catalog."default",
			user_name text COLLATE pg_catalog."default",
			residential_ip boolean,
			connection_quality integer,
			service_commission double precision,
			maximum_active_connections integer,
			test_package_until_traffic_volume double precision,
			test_package_until_traffic_price double precision,
			test_package_for_period_time double precision,
			test_package_for_period_price double precision,
			traffic_vs_period boolean DEFAULT true,
			CONSTRAINT vpn_server_pkey PRIMARY KEY (id)
		)
		TABLESPACE pg_default
		');
		$this->addSql('ALTER TABLE IF EXISTS public.vpn_server OWNER to app;');
        $this->addSql('CREATE SEQUENCE favorite_server_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE vpn_server_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE favorite_server (id INT DEFAULT 1 NOT NULL, user_id INT NOT NULL, server_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX fav_srv_uniq_server_user ON favorite_server (user_id, server_id)');
        //$this->addSql('ALTER TABLE "user" ALTER is_email_verified DROP DEFAULT');
        //$this->addSql('ALTER TABLE "user" ALTER is_two_factor_auth_enabled DROP DEFAULT');
        /*$this->addSql('ALTER TABLE vpn_server ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE vpn_server ALTER country DROP DEFAULT');
        $this->addSql('ALTER TABLE vpn_server ALTER country SET NOT NULL');
        $this->addSql('ALTER TABLE vpn_server ALTER ip DROP DEFAULT');
        $this->addSql('ALTER TABLE vpn_server ALTER created SET NOT NULL');
        $this->addSql('ALTER TABLE vpn_server ALTER modified TYPE TIME(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE vpn_server ALTER modified SET NOT NULL');
        $this->addSql('ALTER TABLE vpn_server ALTER created_by DROP DEFAULT');
        $this->addSql('ALTER TABLE vpn_server ALTER for_free DROP DEFAULT');
        $this->addSql('ALTER TABLE vpn_server ALTER price DROP DEFAULT');
        $this->addSql('ALTER TABLE vpn_server ALTER protocol DROP DEFAULT');
        $this->addSql('ALTER TABLE vpn_server ALTER user_name DROP DEFAULT');
        $this->addSql('ALTER TABLE vpn_server ALTER residential_ip DROP DEFAULT');
        $this->addSql('ALTER TABLE vpn_server ALTER connection_quality DROP DEFAULT');
        $this->addSql('ALTER TABLE vpn_server ALTER service_commission DROP DEFAULT');
        $this->addSql('ALTER TABLE vpn_server ALTER maximum_active_connections DROP DEFAULT');
        $this->addSql('ALTER TABLE vpn_server ALTER test_package_until_traffic_volume DROP DEFAULT');
        $this->addSql('ALTER TABLE vpn_server ALTER test_package_until_traffic_price DROP DEFAULT');
        $this->addSql('ALTER TABLE vpn_server ALTER test_package_for_period_time DROP DEFAULT');
        $this->addSql('ALTER TABLE vpn_server ALTER test_package_for_period_price DROP DEFAULT');*/
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
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE favorite_server_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE vpn_server_id_seq CASCADE');
        $this->addSql('DROP TABLE favorite_server');
        /*$this->addSql('ALTER TABLE vpn_server ALTER id SET DEFAULT 0');
        $this->addSql('ALTER TABLE vpn_server ALTER country SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE vpn_server ALTER country DROP NOT NULL');
        $this->addSql('ALTER TABLE vpn_server ALTER ip SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE vpn_server ALTER created DROP NOT NULL');
        $this->addSql('ALTER TABLE vpn_server ALTER modified TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE vpn_server ALTER modified DROP NOT NULL');
        $this->addSql('ALTER TABLE vpn_server ALTER created_by SET DEFAULT 0');
        $this->addSql('ALTER TABLE vpn_server ALTER for_free SET DEFAULT true');
        $this->addSql('ALTER TABLE vpn_server ALTER price SET DEFAULT \'0\'');
        $this->addSql('ALTER TABLE vpn_server ALTER protocol SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE vpn_server ALTER user_name SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE vpn_server ALTER residential_ip SET DEFAULT false');
        $this->addSql('ALTER TABLE vpn_server ALTER connection_quality SET DEFAULT 0');
        $this->addSql('ALTER TABLE vpn_server ALTER service_commission SET DEFAULT \'0\'');
        $this->addSql('ALTER TABLE vpn_server ALTER maximum_active_connections SET DEFAULT 0');
        $this->addSql('ALTER TABLE vpn_server ALTER test_package_until_traffic_volume SET DEFAULT \'0\'');
        $this->addSql('ALTER TABLE vpn_server ALTER test_package_until_traffic_price SET DEFAULT \'0\'');
        $this->addSql('ALTER TABLE vpn_server ALTER test_package_for_period_time SET DEFAULT \'0\'');
        $this->addSql('ALTER TABLE vpn_server ALTER test_package_for_period_price SET DEFAULT \'0\'');*/
        $this->addSql('COMMENT ON COLUMN vpn_server.created_by IS NULL');
        $this->addSql('COMMENT ON COLUMN vpn_server.for_free IS NULL');
        $this->addSql('COMMENT ON COLUMN vpn_server.protocol IS NULL');
        $this->addSql('COMMENT ON COLUMN vpn_server.residential_ip IS NULL');
        $this->addSql('COMMENT ON COLUMN vpn_server.connection_quality IS NULL');
        $this->addSql('COMMENT ON COLUMN vpn_server.service_commission IS NULL');
        $this->addSql('COMMENT ON COLUMN vpn_server.maximum_active_connections IS NULL');
        $this->addSql('COMMENT ON COLUMN vpn_server.test_package_until_traffic_volume IS NULL');
        $this->addSql('COMMENT ON COLUMN vpn_server.test_package_until_traffic_price IS NULL');
        $this->addSql('COMMENT ON COLUMN vpn_server.test_package_for_period_time IS NULL');
        //$this->addSql('ALTER TABLE "user" ALTER is_email_verified SET DEFAULT false');
        //$this->addSql('ALTER TABLE "user" ALTER is_two_factor_auth_enabled SET DEFAULT false');
    }
}
