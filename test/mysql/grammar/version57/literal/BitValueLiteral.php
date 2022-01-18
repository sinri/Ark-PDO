<?php

namespace sinri\ark\database\test\mysql\grammar\version57\literal;

use InvalidArgumentException;

/**
 * @see https://dev.mysql.com/doc/refman/5.7/en/bit-value-literals.html
 */
class BitValueLiteral extends Literal
{

    public function __construct($bin)
    {
        if (!preg_match('/^[01]+$/', $bin)) {
            throw new InvalidArgumentException("[10]+ !");
        }
        $this->value = $bin;
    }

    public function output(): string
    {
        return '0b' . $this->value;
    }
}