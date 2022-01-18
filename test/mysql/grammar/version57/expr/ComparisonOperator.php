<?php

namespace sinri\ark\database\test\mysql\grammar\version57\expr;

use sinri\ark\database\test\mysql\grammar\SQLComponentInterface;

class ComparisonOperator implements SQLComponentInterface
{
    /**
     * @var string
     */
    protected $raw;

    /**
     * @param string $raw = | >= | > | <= | < | <> | !=
     */
    public function __construct(string $raw)
    {
        $this->raw = $raw;
    }

    public function output(): string
    {
        return $this->raw;
    }
}