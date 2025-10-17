<?php

namespace Roolith\Migration;

trait MigrationUtilTraits
{
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
}
