<?php

namespace Roolith\Migration\Command;

use Roolith\Migration\Command\MigrationCreateCommand;
use Roolith\Store\Database;

class MigrationCommandFactory
{
    /**
     * Create a migration command based on the given command name.
     *
     * @param string $command The command name.
     * @param Database $db The database instance.
     * @param string $fileName The file name.
     * @param array $options The options array.
     * @return BaseCommand The created migration command.
     */
    public static function create(
        string $command,
        Database $db,
        string $fileName,
        array $options,
    ): BaseCommand {
        match ($command) {
            "migration:create" => new MigrationCreateCommand(
                $db,
                $fileName,
                $options,
            ),
            default => throw new \InvalidArgumentException(
                "Unknown command: $command",
            ),
        };
    }
}
