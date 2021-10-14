<?php

namespace sinri\ark\database\model;

trait ArkDatabaseSQLBuilderTrait
{
    abstract public function generateSQL(): string;

    public function __toString()
    {
        return $this->generateSQL();
    }
}