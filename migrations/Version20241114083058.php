<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241114083058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin (id INT AUTO_INCREMENT NOT NULL, pseudo VARCHAR(255) NOT NULL, motdepasse VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE choix (id INT AUTO_INCREMENT NOT NULL, sondage_id INT NOT NULL, titre_choix VARCHAR(255) NOT NULL, image_choix VARCHAR(255) DEFAULT NULL, INDEX IDX_4F488091BAF4AE56 (sondage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE signalement (id INT AUTO_INCREMENT NOT NULL, utilisateur_signale_id INT NOT NULL, utilisateur_signaleur_id INT NOT NULL, raison VARCHAR(255) NOT NULL, INDEX IDX_F4B5511437B960BE (utilisateur_signale_id), INDEX IDX_F4B55114AC9A5FCD (utilisateur_signaleur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sondage (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, titre VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, public TINYINT(1) NOT NULL, date_expiration DATE DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, date_creation DATE NOT NULL, INDEX IDX_7579C89FFB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sondage_theme (sondage_id INT NOT NULL, theme_id INT NOT NULL, INDEX IDX_6C1549B5BAF4AE56 (sondage_id), INDEX IDX_6C1549B559027487 (theme_id), PRIMARY KEY(sondage_id, theme_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, pseudo VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, motdepasse VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, data_naissance DATE NOT NULL, date_fin_bannissement DATE DEFAULT NULL, image_utilisateur VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vote (id INT AUTO_INCREMENT NOT NULL, choix_id INT NOT NULL, utilisateur_id INT NOT NULL, nom_non_inscrit VARCHAR(255) DEFAULT NULL, date_vote DATETIME NOT NULL, ip VARCHAR(255) NOT NULL, INDEX IDX_5A108564D9144651 (choix_id), INDEX IDX_5A108564FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE choix ADD CONSTRAINT FK_4F488091BAF4AE56 FOREIGN KEY (sondage_id) REFERENCES sondage (id)');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B5511437B960BE FOREIGN KEY (utilisateur_signale_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B55114AC9A5FCD FOREIGN KEY (utilisateur_signaleur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE sondage ADD CONSTRAINT FK_7579C89FFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE sondage_theme ADD CONSTRAINT FK_6C1549B5BAF4AE56 FOREIGN KEY (sondage_id) REFERENCES sondage (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sondage_theme ADD CONSTRAINT FK_6C1549B559027487 FOREIGN KEY (theme_id) REFERENCES theme (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564D9144651 FOREIGN KEY (choix_id) REFERENCES choix (id)');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE choix DROP FOREIGN KEY FK_4F488091BAF4AE56');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B5511437B960BE');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B55114AC9A5FCD');
        $this->addSql('ALTER TABLE sondage DROP FOREIGN KEY FK_7579C89FFB88E14F');
        $this->addSql('ALTER TABLE sondage_theme DROP FOREIGN KEY FK_6C1549B5BAF4AE56');
        $this->addSql('ALTER TABLE sondage_theme DROP FOREIGN KEY FK_6C1549B559027487');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A108564D9144651');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A108564FB88E14F');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE choix');
        $this->addSql('DROP TABLE signalement');
        $this->addSql('DROP TABLE sondage');
        $this->addSql('DROP TABLE sondage_theme');
        $this->addSql('DROP TABLE theme');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE vote');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
