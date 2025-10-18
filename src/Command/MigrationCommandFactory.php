<?php

namespace Roolith\Migration\Command;

use Roolith\Migration\Interfaces\CommandInterface;
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
     * @return CommandInterface|null
     */
    public static function create(
        string $command,
        Database $db,
        string $fileName,
        array $options,
    ): ?CommandInterface {
        $commandClassName = self::getCommandClassName($command);
        $commandClassPath = "Roolith\\Migration\\Command\\$commandClassName";

        if (class_exists($commandClassPath)) {
            /** @var CommandInterface $commandInstance */
            $commandInstance = new $commandClassPath($db, $fileName, $options);

            return $commandInstance;
        }

        echo "Unknown command: $command";
        return null;
    }

    /**
     * Get the command class name based on the given command name.
     *
     * @param string $command The command name.
     * @return string The command class name.
     */
    private static function getCommandClassName(string $command): string
    {
        $commandArray = explode(":", $command);
        $commandClassName =
            ucfirst($commandArray[0]) . ucfirst($commandArray[1]) . "Command";

        return $commandClassName;
    }
}
