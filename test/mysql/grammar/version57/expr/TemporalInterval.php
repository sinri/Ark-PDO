<?php

namespace sinri\ark\database\test\mysql\grammar\version57\expr;

use sinri\ark\database\test\mysql\grammar\SQLComponentInterface;

/**
 * @see https://dev.mysql.com/doc/refman/5.7/en/expressions.html#temporal-intervals
 */
class TemporalInterval implements SQLComponentInterface
{
    /**
     * @var numeric
     */
    protected $expr;
    /**
     * @var string
     */
    protected $unit;

    /**
     * @param numeric $expr
     * @param string $unit
     */
    public function __construct($expr, string $unit)
    {
        $this->expr = $expr;
        $this->unit = $unit;
    }

    public function output(): string
    {
        return "INTERVAL $this->expr $this->unit";
    }
}