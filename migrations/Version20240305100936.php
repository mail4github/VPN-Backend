<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240305100936 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE wallet_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE wallet (id INT DEFAULT 1 NOT NULL, user_id INT NOT NULL, address TEXT NOT NULL, active BOOLEAN DEFAULT false, name TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX wallet_uniq_user ON wallet (user_id, address)');
		$this->addSql('CREATE INDEX idx_wallet_name ON wallet (name)');
		$this->addSql('CREATE INDEX idx_wallet_active ON wallet (active)');
        $this->addSql('COMMENT ON COLUMN wallet.address IS \'Address of the user wallet\'');
        $this->addSql('COMMENT ON COLUMN wallet.active IS \'If true then this wallet is active wallet\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE wallet_id_seq CASCADE');
        $this->addSql('DROP TABLE wallet');
    }
}
