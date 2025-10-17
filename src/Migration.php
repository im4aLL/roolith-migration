<?php

namespace Roolith\Migration;

use Roolith\Migration\Interfaces\MigrationCoreInterface;
use Roolith\Store\Database;

class Migration implements MigrationCoreInterface
{
    private string $_folder = "migrations";
    private string $_table = "migrations";
    private array $_dbConfig = [];
    protected Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Set settings
     *
     * @param array $settings
     * @return self
     */
    public function settings(array $settings): self
    {
        if ($settings["folder"]) {
            $this->_folder = $settings["folder"];
        }

        if ($settings["database"]) {
            $this->_dbConfig = $settings["database"];
        }

        $this->_bootstrap();

        return $this;
    }

    /**
     * Bootstrap
     *
     * @return void
     */
    private function _bootstrap(): void
    {
        $this->_createRootFolder();
        $this->_bootstrapDatabase();
    }

    /**
     * Bootstrap database
     *
     * @return void
     */
    private function _bootstrapDatabase(): void
    {
        $this->db->connect($this->_dbConfig);

        $tableData = $this->db
            ->query(
                "SELECT COUNT(*) AS table_exists
                    FROM information_schema.tables
                    WHERE table_schema = '{$this->_dbConfig["name"]}'
                        AND table_name = '{$this->_table}';
                ",
            )
            ->first();

        if ($tableData->table_exists > 0) {
            return;
        }

        $this->db->execute(
            "CREATE TABLE {$this->_table} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
        );
    }

    /**
     * Create root folder
     *
     * @return void
     */
    private function _createRootFolder(): void
    {
        $rootFolder = $this->_folder;

        if (!is_dir($rootFolder)) {
            mkdir($rootFolder, 0755, true);
        }
    }

    /**
     * Run migration
     *
     * @param array $params
     * @return void
     */
    public function run(array $params): void
    {
        $command = $params[1] ?? "";
        $fileName = $params[2] ?? "";

        switch ($command) {
            case "migration:create":
                $this->_createMigrationFile($fileName);
                break;
            case "migration:run":
                $this->_runMigration($fileName);
                break;
            case "migration:rollback":
                $this->_rollbackMigration($fileName);
                break;
            case "migration:status":
                $this->_statusMigration($fileName);
                break;
            default:
                throw new \Exception("Invalid command");
        }

        $this->db->disconnect();
    }

    /**
     * Create migration file
     *
     * @param string $fileName
     * @return void
     */
    private function _createMigrationFile(string $fileName): void
    {
        $file = $this->_folder . DIRECTORY_SEPARATOR . $fileName . ".php";

        if (file_exists($file)) {
            echo "Migration $fileName already exists\n";

            return;
        }

        $template = file_get_contents(__DIR__ . "/template.txt");
        $template = str_replace(
            "{filename}",
            $this->_stringToPascalCase($fileName),
            $template,
        );

        $isCreated = file_put_contents($file, $template);

        $migrationInsertData = [
            "name" => $fileName,
            "created_at" => date("Y-m-d H:i:s"),
        ];

        $record = $this->db->table($this->_table)->insert($migrationInsertData);

        if ($isCreated === false || $record->success() === false) {
            throw new \Exception("Failed to create migration file");
        }

        echo "Migration $fileName created successfully\n";
    }

    /**
     * Convert string to PascalCase
     *
     * @param string $string
     * @return string
     */
    private function _stringToPascalCase(string $string): string
    {
        $string = preg_replace("/[^a-zA-Z0-9]+/", " ", $string);
        $string = ucwords(strtolower(trim($string)));
        $string = str_replace(" ", "", $string);

        return $string;
    }

    /**
     * Run migrations
     *
     * @param string $fileName (name of the migration file - if not provided, run all pending migrations)
     * @return void
     */
    private function _runMigration(string $fileName): void
    {
        if (strlen($fileName) > 0) {
            $migration = $this->db
                ->table($this->_table)
                ->select([
                    "orderBy" => "id ASC",
                ])
                ->where("name", $fileName)
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

    /**
     * Rollback a migration
     *
     * @param $fileName string
     * @return void
     */
    private function _rollbackMigration(string $fileName): void
    {
        $migrationClass =
            $this->_folder . DIRECTORY_SEPARATOR . $fileName . ".php";

        if (!file_exists($migrationClass)) {
            echo "Migration file not found\n";
            return;
        }

        require_once $migrationClass;

        $className = $this->_stringToPascalCase($fileName);
        $migrationInstance = new $className();
        $migrationInstance->down($this->db);

        $this->db
            ->table($this->_table)
            ->update(["status" => "pending"], ["name" => $fileName]);

        echo "Migration $fileName rolled back successfully\n";
    }

    /**
     * Display the status of all migrations.
     *
     * @return void
     */
    private function _statusMigration(string $fileName): void
    {
        if (!$fileName) {
            $this->_getStatusOfMigrations();

            return;
        }

        $this->_getStatusOfMigration($fileName);
    }

    /**
     * Display the status of all migrations.
     *
     * @return void
     */
    private function _getStatusOfMigrations(): void
    {
        $migrations = $this->_db->table($this->_table)->get();

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
        $migration = $this->_db
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
