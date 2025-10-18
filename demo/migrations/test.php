<?php

use Roolith\Store\Database;
use Roolith\Migration\Interfaces\MigrationInterface;

class Test implements MigrationInterface
{
    public function up(Database $db): void {}

    public function down(Database $db): void {}
}
