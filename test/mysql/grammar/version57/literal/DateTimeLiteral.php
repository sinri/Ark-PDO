<?php

namespace sinri\ark\database\test\mysql\grammar\version57\literal;
/**
 * @see https://dev.mysql.com/doc/refman/5.7/en/date-and-time-literals.html
 */
class DateTimeLiteral extends Literal
{
    public function __construct(int $timestamp, $format = 'Y-m-d H:i:s')
    {
        $this->value = date($format, $timestamp);
    }

    public function output(): string
    {
        return $this->value;
    }
}