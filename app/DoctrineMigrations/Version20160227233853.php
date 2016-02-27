<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160227233853 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE etu_argentique_collections (id VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, title VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, description LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, createdAt DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etu_argentique_photos (id VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, title VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, icon VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, file VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, ready TINYINT(1) NOT NULL, createdAt DATETIME DEFAULT NULL, photoSet_id VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci, INDEX IDX_D8AD45EF273EE305 (photoSet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etu_argentique_photos_sets (id VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, collection_id VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci, title VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, icon VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, description LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, createdAt DATETIME DEFAULT NULL, INDEX IDX_57A74CEF514956FD (collection_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etu_users_copy (id INT AUTO_INCREMENT NOT NULL, login VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, password VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, studentId INT DEFAULT NULL, mail VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, fullName VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, firstName VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, lastName VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, formation VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, niveau VARCHAR(10) DEFAULT NULL COLLATE utf8_unicode_ci, filiere VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, phoneNumber VARCHAR(30) DEFAULT NULL COLLATE utf8_unicode_ci, phoneNumberPrivacy INT NOT NULL, title VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, room VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, avatar VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, sex VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci, sexPrivacy INT NOT NULL, nationality VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci, nationalityPrivacy INT NOT NULL, adress VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, adressPrivacy INT NOT NULL, postalCode VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci, postalCodePrivacy INT NOT NULL, city VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, cityPrivacy INT NOT NULL, country VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci, countryPrivacy INT NOT NULL, birthday DATE DEFAULT NULL, birthdayPrivacy INT NOT NULL, birthdayDisplayOnlyAge TINYINT(1) NOT NULL, personnalMail VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, personnalMailPrivacy INT NOT NULL, language VARCHAR(10) DEFAULT NULL COLLATE utf8_unicode_ci, isStudent TINYINT(1) NOT NULL, surnom VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci, jadis LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, passions LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, website VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, facebook VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, twitter VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, linkedin VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, viadeo VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, uvs VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, keepActive TINYINT(1) NOT NULL, permissions LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', removedPermissions LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', isAdmin TINYINT(1) NOT NULL, isReadOnly TINYINT(1) NOT NULL, readOnlyExpirationDate DATETIME NOT NULL, options LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', lastVisitHome DATETIME NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, deletedAt DATETIME DEFAULT NULL, semestersHistory LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', isBanned TINYINT(1) NOT NULL, branch VARCHAR(10) DEFAULT NULL COLLATE utf8_unicode_ci, firstLogin TINYINT(1) NOT NULL, UNIQUE INDEX search (login, mail), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etu_users_courses_save (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, day VARCHAR(20) NOT NULL COLLATE utf8_unicode_ci, start VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, end VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, week VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, uv VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, type VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, room VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, createdAt DATETIME NOT NULL, deletedAt DATETIME DEFAULT NULL, INDEX IDX_5766C6E9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etu_wiki_categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, orga_id INT DEFAULT NULL, title VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, levelToView INT NOT NULL, levelToEdit INT NOT NULL, levelToEditPermissions INT NOT NULL, depth INT NOT NULL, createdAt DATETIME NOT NULL, deletedAt DATETIME DEFAULT NULL, INDEX IDX_4A4F8141727ACA70 (parent_id), INDEX IDX_4A4F814197F068A1 (orga_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etu_wiki_pages (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, revision_id INT DEFAULT NULL, orga_id INT DEFAULT NULL, title VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, levelToView INT NOT NULL, levelToEdit INT NOT NULL, levelToEditPermissions INT NOT NULL, isHome TINYINT(1) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, deletedAt DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_9853E5181DFA7C8F (revision_id), INDEX IDX_9853E51812469DE2 (category_id), INDEX IDX_9853E51897F068A1 (orga_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etu_wiki_pages_revisions (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, page INT DEFAULT NULL, body LONGTEXT NOT NULL COLLATE utf8_unicode_ci, comment VARCHAR(140) DEFAULT NULL COLLATE utf8_unicode_ci, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, deletedAt DATETIME DEFAULT NULL, INDEX IDX_877B4A5EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE etu_argentique_photos ADD CONSTRAINT FK_D8AD45EF273EE305 FOREIGN KEY (photoSet_id) REFERENCES etu_argentique_photos_sets (id)');
        $this->addSql('ALTER TABLE etu_argentique_photos_sets ADD CONSTRAINT FK_57A74CEF514956FD FOREIGN KEY (collection_id) REFERENCES etu_argentique_collections (id)');
        $this->addSql('ALTER TABLE etu_wiki_categories ADD CONSTRAINT FK_4A4F8141727ACA70 FOREIGN KEY (parent_id) REFERENCES etu_wiki_categories (id)');
        $this->addSql('ALTER TABLE etu_wiki_categories ADD CONSTRAINT FK_4A4F814197F068A1 FOREIGN KEY (orga_id) REFERENCES etu_organizations (id)');
        $this->addSql('ALTER TABLE etu_wiki_pages ADD CONSTRAINT FK_9853E51812469DE2 FOREIGN KEY (category_id) REFERENCES etu_wiki_categories (id)');
        $this->addSql('ALTER TABLE etu_wiki_pages ADD CONSTRAINT FK_9853E5181DFA7C8F FOREIGN KEY (revision_id) REFERENCES etu_wiki_pages_revisions (id)');
        $this->addSql('ALTER TABLE etu_wiki_pages ADD CONSTRAINT FK_9853E51897F068A1 FOREIGN KEY (orga_id) REFERENCES etu_organizations (id)');
        $this->addSql('ALTER TABLE etu_wiki_pages_revisions ADD CONSTRAINT FK_877B4A5EA76ED395 FOREIGN KEY (user_id) REFERENCES etu_users (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE etu_argentique_photos_sets DROP FOREIGN KEY FK_57A74CEF514956FD');
        $this->addSql('ALTER TABLE etu_argentique_photos DROP FOREIGN KEY FK_D8AD45EF273EE305');
        $this->addSql('ALTER TABLE etu_wiki_categories DROP FOREIGN KEY FK_4A4F8141727ACA70');
        $this->addSql('ALTER TABLE etu_wiki_pages DROP FOREIGN KEY FK_9853E51812469DE2');
        $this->addSql('ALTER TABLE etu_wiki_pages DROP FOREIGN KEY FK_9853E5181DFA7C8F');
        $this->addSql('DROP TABLE etu_argentique_collections');
        $this->addSql('DROP TABLE etu_argentique_photos');
        $this->addSql('DROP TABLE etu_argentique_photos_sets');
        $this->addSql('DROP TABLE etu_users_copy');
        $this->addSql('DROP TABLE etu_users_courses_save');
        $this->addSql('DROP TABLE etu_wiki_categories');
        $this->addSql('DROP TABLE etu_wiki_pages');
        $this->addSql('DROP TABLE etu_wiki_pages_revisions');
    }

}