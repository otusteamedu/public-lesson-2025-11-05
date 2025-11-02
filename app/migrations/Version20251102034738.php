<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20251102034738 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE client (id SERIAL NOT NULL, login VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql(
            <<<'SQL'
            CREATE TABLE "order" (
              id SERIAL NOT NULL,
              created_by_id INT NOT NULL,
              created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              order_content JSON NOT NULL,
              status VARCHAR(255) NOT NULL,
              PRIMARY KEY(id)
            )
        SQL
        );
        $this->addSql('CREATE INDEX IDX_F5299398B03A8386 ON "order" (created_by_id)');
        $this->addSql(
            <<<'SQL'
            ALTER TABLE
              "order"
            ADD
              CONSTRAINT FK_F5299398B03A8386 FOREIGN KEY (created_by_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL
        );

        $this->addSql("INSERT INTO client (login) values ('first_client')");
    }
}
