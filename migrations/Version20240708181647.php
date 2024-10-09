<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240708181647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added UNIQUE to the idx_administrator_login index';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_administrator_login');
        $this->addSql('CREATE UNIQUE INDEX idx_administrator_login ON administrator (login)');
        
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_administrator_login');
        $this->addSql('CREATE INDEX idx_administrator_login ON administrator (login)');
    }
}
