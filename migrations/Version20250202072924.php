<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250202072924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX IDX_6956883F95079952 ON currency (numeric_code)');
        $this->addSql('CREATE INDEX IDX_6956883F5C4B8DFB ON currency (alpha_code)');
        $this->addSql('CREATE INDEX IDX_6956883F77153098 ON currency (code)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_6956883F95079952 ON currency');
        $this->addSql('DROP INDEX IDX_6956883F5C4B8DFB ON currency');
        $this->addSql('DROP INDEX IDX_6956883F77153098 ON currency');
    }
}
