<?php

namespace AppBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151209235343 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE stress_log (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, time DATETIME NOT NULL, level SMALLINT NOT NULL, notes LONGTEXT DEFAULT NULL, createdAt DATETIME NOT NULL, INDEX IDX_B4947AC8A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stress_manifestation (id INT AUTO_INCREMENT NOT NULL, stress_log_id INT DEFAULT NULL, text VARCHAR(255) NOT NULL, INDEX IDX_45A0D44ADB4FC45D (stress_log_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stress_source (id INT AUTO_INCREMENT NOT NULL, stress_log_id INT DEFAULT NULL, text VARCHAR(255) NOT NULL, INDEX IDX_6EA2E6A8DB4FC45D (stress_log_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, username_canonical VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_canonical VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, locked TINYINT(1) NOT NULL, expired TINYINT(1) NOT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', credentials_expired TINYINT(1) NOT NULL, credentials_expire_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_1483A5E9A0D96FBF (email_canonical), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE stress_log ADD CONSTRAINT FK_B4947AC8A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE stress_manifestation ADD CONSTRAINT FK_45A0D44ADB4FC45D FOREIGN KEY (stress_log_id) REFERENCES stress_log (id)');
        $this->addSql('ALTER TABLE stress_source ADD CONSTRAINT FK_6EA2E6A8DB4FC45D FOREIGN KEY (stress_log_id) REFERENCES stress_log (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE stress_manifestation DROP FOREIGN KEY FK_45A0D44ADB4FC45D');
        $this->addSql('ALTER TABLE stress_source DROP FOREIGN KEY FK_6EA2E6A8DB4FC45D');
        $this->addSql('ALTER TABLE stress_log DROP FOREIGN KEY FK_B4947AC8A76ED395');
        $this->addSql('DROP TABLE stress_log');
        $this->addSql('DROP TABLE stress_manifestation');
        $this->addSql('DROP TABLE stress_source');
        $this->addSql('DROP TABLE users');
    }
}
