<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141121143855 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('CREATE TABLE sb_article_overviewpages (id BIGINT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, page_title VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sb_article_pages (id BIGINT AUTO_INCREMENT NOT NULL, article_author_id BIGINT DEFAULT NULL, date DATETIME NOT NULL, summary LONGTEXT DEFAULT NULL, title VARCHAR(255) NOT NULL, page_title VARCHAR(255) DEFAULT NULL, INDEX IDX_D98516412C40A33F (article_author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sb_article_authors (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, link VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sb_article_pages ADD CONSTRAINT FK_D98516412C40A33F FOREIGN KEY (article_author_id) REFERENCES sb_article_authors (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE sb_article_pages DROP FOREIGN KEY FK_D98516412C40A33F');
        $this->addSql('DROP TABLE sb_article_overviewpages');
        $this->addSql('DROP TABLE sb_article_pages');
        $this->addSql('DROP TABLE sb_article_authors');
    }
}
