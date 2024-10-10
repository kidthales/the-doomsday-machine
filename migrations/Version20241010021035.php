<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\AbstractPostgresSchemaMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20241010021035 extends AbstractPostgresSchemaMigration
{
    protected const string SCHEMA_NAME = 'discord';
}
