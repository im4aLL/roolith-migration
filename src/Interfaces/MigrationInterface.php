<?php

namespace Roolith\Migration\Interfaces;

use Roolith\Store\Database;

interface MigrationInterface
{
    public function up(Database $db): void;

    public function down(Database $db): void;
}
