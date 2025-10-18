<?php

namespace Roolith\Migration;

use Roolith\Migration\Command\MigrationCommandFactory;
use Roolith\Migration\Interfaces\MigrationCoreInterface;
use Roolith\Store\Database;
use Roolith\Store\Interfaces\DatabaseInterface;

class Migration implements MigrationCoreInterface
{
    private string $_folder = "migrations";
    private string $_table = "migrations";
    private array $_dbConfig = [];
    protected DatabaseInterface $db;

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

        $commandInstance = MigrationCommandFactory::create(
            $command,
            $this->db,
            $fileName,
            [
                "folder" => $this->_folder,
                "table" => $this->_table,
            ],
        );

        if ($commandInstance) {
            $commandInstance->execute();
        }

        $this->db->disconnect();
    }
}
