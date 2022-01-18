<?php

namespace sinri\ark\database\test\mysql\grammar\version57\literal;
/**
 * @see https://dev.mysql.com/doc/refman/5.7/en/boolean-literals.html
 */
class BooleanLiteral extends Literal
{

    public function __construct(bool $boolean)
    {
        $this->value = $boolean;
    }

    public function output(): string
    {
        return $this->value ? 'TRUE' : 'FALSE';
    }
}