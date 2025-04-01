<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250331150457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favorite DROP FOREIGN KEY FK_68C58ED97294869C');
        $this->addSql('DROP INDEX IDX_68C58ED97294869C ON favorite');
        $this->addSql('ALTER TABLE favorite CHANGE article_id actuality_id INT NOT NULL');
        $this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED9B84BD854 FOREIGN KEY (actuality_id) REFERENCES actuality (id)');
        $this->addSql('CREATE INDEX IDX_68C58ED9B84BD854 ON favorite (actuality_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favorite DROP FOREIGN KEY FK_68C58ED9B84BD854');
        $this->addSql('DROP INDEX IDX_68C58ED9B84BD854 ON favorite');
        $this->addSql('ALTER TABLE favorite CHANGE actuality_id article_id INT NOT NULL');
        $this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED97294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_68C58ED97294869C ON favorite (article_id)');
    }
}
