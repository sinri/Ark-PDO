<?php

namespace sinri\ark\database\test\mysql\grammar\version57\literal;

class NullLiteral extends Literal
{

    public function output(): string
    {
        return 'NULL';
    }
}