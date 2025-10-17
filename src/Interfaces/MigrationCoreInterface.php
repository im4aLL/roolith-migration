<?php

namespace Roolith\Migration\Interfaces;

interface MigrationCoreInterface
{
    /**
     * Set the settings for the migration.
     *
     * @param array $settings
     * @return self
     */
    public function settings(array $settings): self;

    /**
     * Run the migration.
     *
     * @param array $params
     * @return void
     */
    public function run(array $params): void;
}
