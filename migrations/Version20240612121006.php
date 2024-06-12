<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240612121006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'adding table for coupon types';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE coupon_type_id_seq CASCADE');
        $this->addSql('CREATE TABLE coupon_type (code VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(code))');
        $this->addSql('ALTER TABLE discount_coupon ADD coupon_type_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE discount_coupon ADD CONSTRAINT FK_AFF133D2A24CF05 FOREIGN KEY (coupon_type_id) REFERENCES coupon_type (code) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_AFF133D2A24CF05 ON discount_coupon (coupon_type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE discount_coupon DROP CONSTRAINT FK_AFF133D2A24CF05');
        $this->addSql('CREATE SEQUENCE coupon_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('DROP TABLE coupon_type');
        $this->addSql('DROP INDEX IDX_AFF133D2A24CF05');
        $this->addSql('ALTER TABLE discount_coupon DROP coupon_type_id');
    }
}
