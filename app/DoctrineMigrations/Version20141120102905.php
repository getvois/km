<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141120102905 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('CREATE TABLE sb_company_authors (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, link VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sb_company_overviewpages (id BIGINT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, page_title VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sb_company_pages (id BIGINT AUTO_INCREMENT NOT NULL, company_author_id BIGINT DEFAULT NULL, date DATETIME NOT NULL, summary LONGTEXT DEFAULT NULL, title VARCHAR(255) NOT NULL, page_title VARCHAR(255) DEFAULT NULL, INDEX IDX_E9C3C635C56E79EB (company_author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sb_company_pages ADD CONSTRAINT FK_E9C3C635C56E79EB FOREIGN KEY (company_author_id) REFERENCES sb_company_authors (id)');
        $this->addSql('DROP TABLE news_place');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE sb_company_pages DROP FOREIGN KEY FK_E9C3C635C56E79EB');
        $this->addSql('CREATE TABLE news_place (newspage_id BIGINT NOT NULL, placeoverviewpage_id BIGINT NOT NULL, INDEX IDX_EA2D49379DC4713 (newspage_id), INDEX IDX_EA2D493E534371D (placeoverviewpage_id), PRIMARY KEY(newspage_id, placeoverviewpage_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE news_place ADD CONSTRAINT FK_EA2D49379DC4713 FOREIGN KEY (newspage_id) REFERENCES sb_news_pages (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE news_place ADD CONSTRAINT FK_EA2D493E534371D FOREIGN KEY (placeoverviewpage_id) REFERENCES sb_place_overviewpages (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE sb_company_authors');
        $this->addSql('DROP TABLE sb_company_overviewpages');
        $this->addSql('DROP TABLE sb_company_pages');
    }
}
