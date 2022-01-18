<?php

namespace sinri\ark\database\test\mysql\grammar\version57\statement;

use sinri\ark\database\test\mysql\grammar\SQLComponentInterface;

class SelectStatement implements SQLComponentInterface
{
//    /**
//     * @var string ALL | DISTINCT | DISTINCTROW
//     */
//    public $distinctFlag="";
//    /**
//     * @var bool HIGH_PRIORITY
//     */
//    public $highPriorityFlag;
//    /**
//     * @var bool STRAIGHT_JOIN
//     */
//    public $straightJoinFlag;

    /**
     * [ALL | DISTINCT | DISTINCTROW ]
     * [HIGH_PRIORITY]
     * [STRAIGHT_JOIN]
     * [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
     * [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
     * @var string[]
     */
    protected $prefixList = [];

    public function output(): string
    {
        // TODO: Implement output() method.
    }
}