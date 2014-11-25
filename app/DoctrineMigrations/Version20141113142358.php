<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141113142358 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('CREATE TABLE sb_place_authors (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, link VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sb_place_overviewpages (id BIGINT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, page_title VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sb_place_pages (id BIGINT AUTO_INCREMENT NOT NULL, place_author_id BIGINT DEFAULT NULL, date DATETIME NOT NULL, summary LONGTEXT DEFAULT NULL, title VARCHAR(255) NOT NULL, page_title VARCHAR(255) DEFAULT NULL, INDEX IDX_85700A8B82EE7A66 (place_author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sb_place_pages ADD CONSTRAINT FK_85700A8B82EE7A66 FOREIGN KEY (place_author_id) REFERENCES sb_place_authors (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE sb_place_pages DROP FOREIGN KEY FK_85700A8B82EE7A66');
        $this->addSql('DROP TABLE sb_place_authors');
        $this->addSql('DROP TABLE sb_place_overviewpages');
        $this->addSql('DROP TABLE sb_place_pages');
    }
}
