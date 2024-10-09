<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240401181243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE transaction_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE transaction (
			id INT DEFAULT 1 NOT NULL, 
			user_id INT NOT NULL, 
			created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
			modified TIME(0) WITHOUT TIME ZONE NOT NULL, 
			tr_type VARCHAR(3) DEFAULT \'ADD\' NOT NULL, 
			amount DOUBLE PRECISION DEFAULT \'0\' NOT NULL, 
			currency VARCHAR(5) DEFAULT \'NDS\' NOT NULL, 
			status VARCHAR(2) DEFAULT \'A\' NOT NULL, 
			date_will_active TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
			description VARCHAR(255) DEFAULT NULL, 
			balance DOUBLE PRECISION DEFAULT NULL, 
			txid VARCHAR(1024) DEFAULT \'\', 
			crc VARCHAR(255) DEFAULT \'\', 
			PRIMARY KEY(id)
			)');
		$this->addSql('CREATE INDEX IDX_transaction_user_id ON transaction (user_id)');
		$this->addSql('CREATE INDEX IDX_transaction_created ON transaction (created)');
		$this->addSql('CREATE INDEX IDX_transaction_modified ON transaction (modified)');
		$this->addSql('CREATE INDEX IDX_transaction_tr_type ON transaction (tr_type)');
		$this->addSql('CREATE INDEX IDX_transaction_amount ON transaction (amount)');
		$this->addSql('CREATE INDEX IDX_transaction_currency ON transaction (currency)');
		$this->addSql('CREATE INDEX IDX_transaction_status ON transaction (status)');
		
        $this->addSql('COMMENT ON COLUMN transaction.user_id IS \'ID of the owner of this transacrion in the User table\'');
        $this->addSql('COMMENT ON COLUMN transaction.tr_type IS \'The transaction type. Values: ADD - Accrual, SUB - Charging-off\'');
        $this->addSql('COMMENT ON COLUMN transaction.amount IS \'The value of transaction\'');
        $this->addSql('COMMENT ON COLUMN transaction.status IS \'A - approved, P - pending, D - declined\'');
        $this->addSql('COMMENT ON COLUMN transaction.date_will_active IS \'A date when this transaction will be activated automatically\'');
        $this->addSql('COMMENT ON COLUMN transaction.balance IS \'The balance for that currency calculated at the moment of transaction\'');
        $this->addSql('COMMENT ON COLUMN transaction.txid IS \'Transaction hash from blockchain\'');
        $this->addSql('COMMENT ON COLUMN transaction.crc IS \'The control sum of this transaction\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE transaction_id_seq CASCADE');
        $this->addSql('DROP TABLE transaction');
    }
}
