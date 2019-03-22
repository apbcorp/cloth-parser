<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190322150919 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE `approvedProductParams` (
                `id` INT AUTO_INCREMENT NOT NULL,
                `productId` INT NOT NULL,
                `param` VARCHAR(256) NOT NULL,
                `value` LONGTEXT NOT NULL,
                PRIMARY KEY(id))
            DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE `approvedProductParams`');
    }
}
