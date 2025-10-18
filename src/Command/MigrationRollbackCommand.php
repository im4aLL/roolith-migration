<?php

namespace Roolith\Migration\Command;

use Roolith\Migration\MigrationUtilTraits;

class MigrationRollbackCommand extends BaseCommand
{
    use MigrationUtilTraits;

    public function execute(): void
    {
        $migrationClass =
            $this->_folder . DIRECTORY_SEPARATOR . $this->fileName . ".php";

        if (!file_exists($migrationClass)) {
            echo "Migration file not found\n";
            return;
        }

        require_once $migrationClass;

        $className = $this->_stringToPascalCase($this->fileName);
        $migrationInstance = new $className();
        $migrationInstance->down($this->db);

        $this->db
            ->table($this->_table)
            ->update(["status" => "pending"], ["name" => $this->fileName]);

        echo "Migration {$this->fileName} rolled back successfully\n";
    }
}
