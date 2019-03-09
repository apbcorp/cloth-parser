<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190305111232 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE `products` (
                `id` INT AUTO_INCREMENT NOT NULL,
                `project` VARCHAR(255) NOT NULL,
                `link` VARCHAR(255) NOT NULL,
                PRIMARY KEY(id),
                UNIQUE `project_link`(`project`, `link`))
            DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `productParams` (
                `id` INT AUTO_INCREMENT NOT NULL,
                `productId` INT NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `value` LONGTEXT NOT NULL,
                PRIMARY KEY(id),
                UNIQUE `product_name` (`productId`, `name`))
            DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE `products`');
        $this->addSql('DROP TABLE `productParams`');
    }
}
