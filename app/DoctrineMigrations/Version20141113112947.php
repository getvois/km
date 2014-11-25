<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141113112947 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('CREATE TABLE sb_news_page_parts (id BIGINT AUTO_INCREMENT NOT NULL, image_id BIGINT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, sub_title VARCHAR(255) DEFAULT NULL, short_desc LONGTEXT DEFAULT NULL, text LONGTEXT DEFAULT NULL, image_alt_text LONGTEXT DEFAULT NULL, INDEX IDX_91F262463DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sb_news_page_parts ADD CONSTRAINT FK_91F262463DA5256D FOREIGN KEY (image_id) REFERENCES kuma_media (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('DROP TABLE sb_news_page_parts');
    }
}
