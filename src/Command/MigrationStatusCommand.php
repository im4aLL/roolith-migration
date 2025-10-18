<?php

namespace Roolith\Migration\Command;

class MigrationStatusCommand extends BaseCommand
{
    public function execute(): void
    {
        if (!$this->fileName) {
            $this->_getStatusOfMigrations();

            return;
        }

        $this->_getStatusOfMigration($this->fileName);
    }

    /**
     * Display the status of all migrations.
     *
     * @return void
     */
    private function _getStatusOfMigrations(): void
    {
        $migrations = $this->db->table($this->_table)->get();

        foreach ($migrations as $migration) {
            if ($migration->status === "completed") {
                echo "Migration {$migration->name} status: completed\n";
            } elseif ($migration->status === "pending") {
                echo "Migration {$migration->name} status: pending\n";
            } else {
                echo "Migration {$migration->name} status: unknown\n";
            }
        }
    }

    /**
     * Display the status of a specific migration.
     *
     * @param string $fileName The name of the migration file.
     * @return void
     */
    private function _getStatusOfMigration(string $fileName): void
    {
        $migration = $this->db
            ->table($this->_table)
            ->where("name", $fileName)
            ->first();

        if (!$migration) {
            echo "Migration not found\n";
            return;
        }

        $migrationClass =
            $this->_folder . DIRECTORY_SEPARATOR . $fileName . ".php";

        if (!file_exists($migrationClass)) {
            echo "Migration file not found\n";
            return;
        }

        if ($migration->status === "completed") {
            echo "Migration $fileName status: completed\n";
        } elseif ($migration->status === "pending") {
            echo "Migration $fileName status: pending\n";
        } else {
            echo "Migration $fileName status: unknown\n";
        }
    }
}
