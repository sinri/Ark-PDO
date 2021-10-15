<?php

namespace sinri\ark\database\model;
/**
 * @since 2.1 reconstructed
 */
trait ArkDatabaseSQLBuilderTrait
{
    abstract public function generateSQL(): string;

    public function __toString()
    {
        return $this->generateSQL();
    }
}