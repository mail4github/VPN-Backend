<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240221105926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE vpn_server ADD test_packages TEXT DEFAULT \'[]\'');
		$this->addSql('ALTER TABLE vpn_server ADD paid_packages TEXT DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE vpn_server ALTER password SET DEFAULT \'\'');
        $this->addSql('COMMENT ON COLUMN vpn_server.test_packages IS \'a JSON text with array of test packages\'');
		$this->addSql('COMMENT ON COLUMN vpn_server.paid_packages IS \'a JSON text with array of paid packages\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE vpn_server DROP paid_packages');
		$this->addSql('ALTER TABLE vpn_server DROP test_packages');
        $this->addSql('ALTER TABLE vpn_server ALTER password DROP DEFAULT');
    }
}
