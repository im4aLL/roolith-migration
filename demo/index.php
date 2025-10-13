<?php

use Roolith\Migration\Migration;

require __DIR__ . "/../vendor/autoload.php";

$migration = new Migration();
$migration
    ->settings([
        "folder" => __DIR__ . "/migrations",
        "database" => [
            "host" => "localhost",
            "name" => "roolith_cms",
            "user" => "root",
            "pass" => "hadi",
        ],
    ])
    ->run($argv);
