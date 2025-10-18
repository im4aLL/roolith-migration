<?php

use Roolith\Migration\Interfaces\MigrationInterface;
use Roolith\Store\Interfaces\DatabaseInterface;

class Test implements MigrationInterface
{
    public function up(DatabaseInterface $db): void {}

    public function down(DatabaseInterface $db): void {}
}
