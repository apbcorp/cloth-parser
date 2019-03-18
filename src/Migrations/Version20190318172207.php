<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190318172207 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE `product` ADD COLUMN `status` int NOT NULL');

    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE `product` DROP COLUMN `status`');
    }
}
