<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210131082644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add rate table';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS rate
  (
     id       INT auto_increment,
     `date`   DATE NOT NULL,
     base     VARCHAR(3) NOT NULL,
     quote    VARCHAR(3) NOT NULL,
     price    DECIMAL(20, 6) NOT NULL,
     provider VARCHAR(10) NOT NULL,
     created  DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
     updated  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
     CONSTRAINT rate_pk PRIMARY KEY (id)
  );

CREATE INDEX rate_base_index ON rate (base);

CREATE UNIQUE INDEX rate_date_base_quote_provider_uindex ON rate (`date`, base,
quote, provider);

CREATE INDEX rate_quote_index ON rate (quote); 
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<SQL
DROP TABLE rate;
SQL;
        $this->addSql($sql);
    }
}
