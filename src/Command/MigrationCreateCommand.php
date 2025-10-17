<?php

namespace Roolith\Migration\Command;

use Roolith\Migration\MigrationUtilTraits;

class MigrationCreateCommand extends BaseCommand
{
    use MigrationUtilTraits;

    /**
     * Create migration file
     *
     * @return void
     */
    public function execute(): void
    {
        $file = $this->_folder . DIRECTORY_SEPARATOR . $this->fileName . ".php";

        if (file_exists($file)) {
            echo "Migration {$this->fileName} already exists\n";

            return;
        }

        $template = file_get_contents(__DIR__ . "/../template.txt");
        $template = str_replace(
            "{filename}",
            $this->_stringToPascalCase($this->fileName),
            $template,
        );

        $isCreated = file_put_contents($file, $template);

        $migrationInsertData = [
            "name" => $this->fileName,
            "created_at" => date("Y-m-d H:i:s"),
        ];

        $record = $this->db->table($this->_table)->insert($migrationInsertData);

        if ($isCreated === false || $record->success() === false) {
            throw new \Exception("Failed to create migration file");
        }

        echo "Migration {$this->fileName} created successfully\n";
    }
}
