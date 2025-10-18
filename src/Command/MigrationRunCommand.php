<?php

namespace Roolith\Migration\Command;

use Roolith\Migration\MigrationUtilTraits;

class MigrationRunCommand extends BaseCommand
{
    use MigrationUtilTraits;

    /**
     * Execute migration command
     *
     * @return void
     */
    public function execute(): void
    {
        if (strlen($this->fileName) > 0) {
            $migration = $this->db
                ->table($this->_table)
                ->select([
                    "orderBy" => "id ASC",
                ])
                ->where("name", $this->fileName)
                ->where("status", "pending")
                ->first();

            $migrations = $migration ? [$migration] : [];
        } else {
            $migrations = $this->db
                ->table($this->_table)
                ->select([
                    "orderBy" => "id ASC",
                ])
                ->where("status", "pending")
                ->get();
        }

        if (count($migrations) === 0) {
            echo "No pending migrations found.\n";

            return;
        }

        $this->_executeMigration($migrations);
    }

    /**
     * Execute migrations
     *
     * @param array $migrations Array of migrations to execute
     * @return void
     */
    private function _executeMigration(array $migrations): void
    {
        foreach ($migrations as $migration) {
            $migrationClass =
                $this->_folder .
                DIRECTORY_SEPARATOR .
                $migration->name .
                ".php";

            if (!file_exists($migrationClass)) {
                throw new \Exception("Migration file not found");
            }

            require_once $migrationClass;

            $className = $this->_stringToPascalCase($migration->name);
            $migrationInstance = new $className();
            $migrationInstance->up($this->db);

            $this->db
                ->table($this->_table)
                ->update(["status" => "completed"], ["id" => $migration->id]);

            echo "Migration $migration->name executed successfully\n";
        }
    }
}
