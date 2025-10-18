<?php

namespace Roolith\Migration\Command;

use Roolith\Migration\Interfaces\CommandInterface;
use Roolith\Store\Database;

abstract class BaseCommand implements CommandInterface
{
    protected Database $db;
    protected string $fileName;
    protected array $options;

    protected string $_folder;
    protected string $_table;

    /**
     * Constructor for BaseCommand.
     *
     * @param Database $db The database instance.
     * @param string $fileName The file name.
     * @param array $options The options array.
     */
    public function __construct(Database $db, string $fileName, array $options)
    {
        $this->db = $db;
        $this->fileName = $fileName;
        $this->options = $options;

        $this->_folder = $options["folder"];
        $this->_table = $options["table"];
    }

    abstract public function execute(): void;
}
