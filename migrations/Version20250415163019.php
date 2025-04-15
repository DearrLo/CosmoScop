<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250415163019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favorite DROP FOREIGN KEY FK_68C58ED9B84BD854');
        $this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED9B84BD854 FOREIGN KEY (actuality_id) REFERENCES actuality (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favorite DROP FOREIGN KEY FK_68C58ED9B84BD854');
        $this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED9B84BD854 FOREIGN KEY (actuality_id) REFERENCES actuality (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
