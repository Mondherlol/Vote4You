<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250107175633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC2EE78D6C');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA2EE78D6C');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B551142EDE39AD');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B5511445A4073C');
        $this->addSql('ALTER TABLE sondage DROP FOREIGN KEY FK_7579C89F2EE78D6C');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A10856479F37AE5');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, profile_pic VARCHAR(1000) DEFAULT NULL, date_fin_ban DATETIME DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', username VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP INDEX IDX_67F068BC2EE78D6C ON commentaire');
        $this->addSql('ALTER TABLE commentaire CHANGE id_owner_id owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_67F068BC7E3C61F9 ON commentaire (owner_id)');
        $this->addSql('DROP INDEX IDX_BF5476CA2EE78D6C ON notification');
        $this->addSql('ALTER TABLE notification CHANGE id_owner_id owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA7E3C61F9 ON notification (owner_id)');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B551142EDE39AD');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B5511445A4073C');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B551142EDE39AD FOREIGN KEY (user_signaler_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B5511445A4073C FOREIGN KEY (user_signaleur_id) REFERENCES user (id)');
        $this->addSql('DROP INDEX IDX_7579C89F2EE78D6C ON sondage');
        $this->addSql('ALTER TABLE sondage CHANGE id_owner_id owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE sondage ADD CONSTRAINT FK_7579C89F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_7579C89F7E3C61F9 ON sondage (owner_id)');
        $this->addSql('DROP INDEX IDX_5A10856479F37AE5 ON vote');
        $this->addSql('ALTER TABLE vote CHANGE id_user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5A108564A76ED395 ON vote (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC7E3C61F9');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA7E3C61F9');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B551142EDE39AD');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B5511445A4073C');
        $this->addSql('ALTER TABLE sondage DROP FOREIGN KEY FK_7579C89F7E3C61F9');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A108564A76ED395');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, pseudo VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_fin_ban DATE DEFAULT NULL, profile_pic VARCHAR(1000) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP INDEX IDX_67F068BC7E3C61F9 ON commentaire');
        $this->addSql('ALTER TABLE commentaire CHANGE owner_id id_owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC2EE78D6C FOREIGN KEY (id_owner_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_67F068BC2EE78D6C ON commentaire (id_owner_id)');
        $this->addSql('DROP INDEX IDX_BF5476CA7E3C61F9 ON notification');
        $this->addSql('ALTER TABLE notification CHANGE owner_id id_owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA2EE78D6C FOREIGN KEY (id_owner_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA2EE78D6C ON notification (id_owner_id)');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B551142EDE39AD');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B5511445A4073C');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B551142EDE39AD FOREIGN KEY (user_signaler_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B5511445A4073C FOREIGN KEY (user_signaleur_id) REFERENCES utilisateur (id)');
        $this->addSql('DROP INDEX IDX_7579C89F7E3C61F9 ON sondage');
        $this->addSql('ALTER TABLE sondage CHANGE owner_id id_owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE sondage ADD CONSTRAINT FK_7579C89F2EE78D6C FOREIGN KEY (id_owner_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_7579C89F2EE78D6C ON sondage (id_owner_id)');
        $this->addSql('DROP INDEX IDX_5A108564A76ED395 ON vote');
        $this->addSql('ALTER TABLE vote CHANGE user_id id_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A10856479F37AE5 FOREIGN KEY (id_user_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_5A10856479F37AE5 ON vote (id_user_id)');
    }
}
