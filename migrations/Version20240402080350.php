<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240402080350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction ADD items_spent DOUBLE PRECISION DEFAULT \'0\'');
        $this->addSql('ALTER TABLE transaction ADD items_total DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD item_name VARCHAR(32) DEFAULT \'Day\'');
        $this->addSql('COMMENT ON COLUMN transaction.items_spent IS \'Number of access entities which user spent to get access to the server\'');
        $this->addSql('COMMENT ON COLUMN transaction.items_total IS \'Total number of access entities which have been purchased by user to get access to the server\'');
        $this->addSql('COMMENT ON COLUMN transaction.item_name IS \'Name of access entities which have been purchased by user to get access to the server. Example: Day, Gb\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction DROP items_spent');
        $this->addSql('ALTER TABLE transaction DROP items_total');
        $this->addSql('ALTER TABLE transaction DROP item_name');
    }
}
