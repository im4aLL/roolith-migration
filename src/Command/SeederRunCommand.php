<?php

namespace Roolith\Migration\Command;

use Roolith\Migration\Interfaces\SeederInterface;
use Roolith\Migration\MigrationUtilTraits;

class SeederRunCommand extends BaseCommand
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
            echo "No pending seeder found.\n";

            return;
        }

        $this->_executeSeeder($migrations);
    }

    /**
     * Execute seeders
     *
     * @param array $migrations Array of seeders to execute
     * @return void
     */
    private function _executeSeeder(array $migrations): void
    {
        foreach ($migrations as $migration) {
            $migrationClass =
                $this->_folder .
                DIRECTORY_SEPARATOR .
                $migration->name .
                ".php";

            if (!file_exists($migrationClass)) {
                throw new \Exception("Seeder file not found");
            }

            require_once $migrationClass;

            $className = $this->_stringToPascalCase($migration->name);
            /* @var SeederInterface $migrationInstance */
            $migrationInstance = new $className();
            $migrationInstance->run($this->db);

            $this->db
                ->table($this->_table)
                ->update(["status" => "completed"], ["id" => $migration->id]);

            echo "Seeder $migration->name executed successfully\n";
        }
    }
}
