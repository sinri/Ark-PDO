<?php

namespace sinri\ark\database\test\mysql\grammar\version57\object;

use sinri\ark\database\test\mysql\grammar\SQLComponentInterface;

/**
 * @see https://dev.mysql.com/doc/refman/5.7/en/user-variables.html
 */
class UserDefinedVariableName implements SQLComponentInterface
{
    /**
     * @var string
     */
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function output(): string
    {
        return '@' . $this->name;// @name or @`name`
    }
}