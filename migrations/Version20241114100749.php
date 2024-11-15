<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241114100749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin (id INT AUTO_INCREMENT NOT NULL, pseudo VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sondage (id INT AUTO_INCREMENT NOT NULL, id_owner_id INT NOT NULL, titre VARCHAR(255) NOT NULL, description VARCHAR(500) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, publique TINYINT(1) NOT NULL, date_fin DATETIME NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7579C89F2EE78D6C (id_owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sondage ADD CONSTRAINT FK_7579C89F2EE78D6C FOREIGN KEY (id_owner_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sondage DROP FOREIGN KEY FK_7579C89F2EE78D6C');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE sondage');
    }
}
