<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241219204836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signalement ADD user_signaleur_id INT NOT NULL');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B5511445A4073C FOREIGN KEY (user_signaleur_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_F4B5511445A4073C ON signalement (user_signaleur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B5511445A4073C');
        $this->addSql('DROP INDEX IDX_F4B5511445A4073C ON signalement');
        $this->addSql('ALTER TABLE signalement DROP user_signaleur_id');
    }
}
