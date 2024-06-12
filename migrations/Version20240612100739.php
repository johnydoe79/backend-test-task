<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240612100739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'change id field for discount coupons table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE discount_coupon_id_seq CASCADE');
        $this->addSql('ALTER TABLE discount_coupon DROP CONSTRAINT discount_coupon_pkey');
        $this->addSql('ALTER TABLE discount_coupon DROP id');
        $this->addSql('ALTER TABLE discount_coupon ADD PRIMARY KEY (coupon_code)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE discount_coupon_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('DROP INDEX discount_coupon_pkey');
        $this->addSql('ALTER TABLE discount_coupon ADD id INT NOT NULL');
        $this->addSql('ALTER TABLE discount_coupon ADD PRIMARY KEY (id)');
    }
}
