<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141113130420 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('CREATE TABLE sb_news_authors (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, link VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sb_news_pages (id BIGINT AUTO_INCREMENT NOT NULL, news_author_id BIGINT DEFAULT NULL, date DATETIME NOT NULL, summary LONGTEXT DEFAULT NULL, title VARCHAR(255) NOT NULL, page_title VARCHAR(255) DEFAULT NULL, INDEX IDX_9E2487E0EA50ACDA (news_author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sb_news_overviewpages (id BIGINT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, page_title VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sb_news_pages ADD CONSTRAINT FK_9E2487E0EA50ACDA FOREIGN KEY (news_author_id) REFERENCES sb_news_authors (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE sb_news_pages DROP FOREIGN KEY FK_9E2487E0EA50ACDA');
        $this->addSql('DROP TABLE sb_news_authors');
        $this->addSql('DROP TABLE sb_news_pages');
        $this->addSql('DROP TABLE sb_news_overviewpages');
    }
}
