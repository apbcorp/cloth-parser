<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190315170311 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE `product` (
                `id` INT AUTO_INCREMENT NOT NULL,
                `projectId` INT NOT NULL,
                `link` VARCHAR(1024) NOT NULL,
                `code` LONGTEXT NOT NULL,
                PRIMARY KEY(id),
                UNIQUE `link`(`link`))
            DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE `product`');
    }
}
