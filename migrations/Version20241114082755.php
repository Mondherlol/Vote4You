<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241114082755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin (id INT AUTO_INCREMENT NOT NULL, pseudo VARCHAR(255) NOT NULL, motdepasse VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE signalement (id INT AUTO_INCREMENT NOT NULL, utilisateur_signale_id INT NOT NULL, utilisateur_signaleur_id INT NOT NULL, raison VARCHAR(255) NOT NULL, INDEX IDX_F4B5511437B960BE (utilisateur_signale_id), INDEX IDX_F4B55114AC9A5FCD (utilisateur_signaleur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vote (id INT AUTO_INCREMENT NOT NULL, choix_id INT NOT NULL, utilisateur_id INT NOT NULL, nom_non_inscrit VARCHAR(255) DEFAULT NULL, date_vote DATETIME NOT NULL, ip VARCHAR(255) NOT NULL, INDEX IDX_5A108564D9144651 (choix_id), INDEX IDX_5A108564FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B5511437B960BE FOREIGN KEY (utilisateur_signale_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B55114AC9A5FCD FOREIGN KEY (utilisateur_signaleur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564D9144651 FOREIGN KEY (choix_id) REFERENCES choix (id)');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B5511437B960BE');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B55114AC9A5FCD');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A108564D9144651');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A108564FB88E14F');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE signalement');
        $this->addSql('DROP TABLE vote');
    }
}
