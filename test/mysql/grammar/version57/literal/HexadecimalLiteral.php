<?php

namespace sinri\ark\database\test\mysql\grammar\version57\literal;

use InvalidArgumentException;

/**
 * @see https://dev.mysql.com/doc/refman/5.7/en/hexadecimal-literals.html
 */
class HexadecimalLiteral extends Literal
{
    public function __construct(string $hex)
    {
        if (!preg_match('/^[0-9A-Fa-f]+$/', $hex)) {
            throw new InvalidArgumentException("[0-9A-Fa-f]+ !");
        }
        $this->value = $hex;
    }

    public function output(): string
    {
        return '0x' . $this->value;
    }
}