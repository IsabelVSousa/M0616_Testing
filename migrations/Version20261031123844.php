<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261031123844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D27E6D43BFDFB4D8 ON nurse (internal_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D27E6D43EC7E7152 ON nurse (license_number)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D27E6D43F85E0677 ON nurse (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D27E6D43E7927C74 ON nurse (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_D27E6D43BFDFB4D8 ON nurse');
        $this->addSql('DROP INDEX UNIQ_D27E6D43EC7E7152 ON nurse');
        $this->addSql('DROP INDEX UNIQ_D27E6D43F85E0677 ON nurse');
        $this->addSql('DROP INDEX UNIQ_D27E6D43E7927C74 ON nurse');
    }
}
