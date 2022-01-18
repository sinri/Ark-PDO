<?php

namespace sinri\ark\database\test\mysql\grammar\version57\expr;

use sinri\ark\database\test\mysql\grammar\SQLComponentInterface;
use sinri\ark\database\test\mysql\grammar\version57\literal\BooleanLiteral;

class Expr implements SQLComponentInterface
{
    /**
     * @var string
     */
    protected $rawExpr;

    /**
     * @param string $rawExpr
     */
    protected function __construct(string $rawExpr)
    {
        $this->rawExpr = $rawExpr;
    }

    public static function makeFromBooleanPrimary(BooleanPrimary $booleanPrimary)
    {
        return new self($booleanPrimary->output());
    }

    public static function makeFromBooleanPrimaryIsBoolean(BooleanPrimary $booleanPrimary, $operator, BooleanLiteral $bl = null)
    {
        return new self($booleanPrimary->output() . ' ' . $operator . ' ' . ($bl === null ? 'UNKNOWN' : $bl->output()));
    }

    /**
     * @param string $operator OR, ||, XOR, AND, &&
     * @param Expr $anotherExpr
     * @return $this
     */
    public function operateAgainstAnother(string $operator, Expr $anotherExpr)
    {
        $this->rawExpr .= " " . $operator . " " . $anotherExpr->output();
        return $this;
    }

    public function output(): string
    {
        return $this->rawExpr;
    }

    /**
     * @param string $prefix NOT !
     * @return $this
     */
    public function addPrefix(string $prefix)
    {
        $this->rawExpr = $prefix . " " . $this->rawExpr;
        return $this;
    }
}