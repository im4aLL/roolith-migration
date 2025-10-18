<?php

namespace Roolith\Migration\Interfaces;

use Roolith\Store\Database;

interface SeederInterface
{
    public function run(Database $db): void;
}
