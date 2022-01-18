<?php

namespace sinri\ark\database\test\mysql\grammar\version57\expr;

use sinri\ark\database\test\mysql\grammar\SQLComponentInterface;
use sinri\ark\database\test\mysql\grammar\version57\statement\SelectStatement;

class Predicate implements SQLComponentInterface
{
    /**
     * @var string
     */
    protected $raw;

    public function __construct(BitExpr $bitExpr)
    {
        $this->raw = $bitExpr->output();
    }

    public function inSubQuery(SelectStatement $selectStatement, bool $withNot = false)
    {
        $this->raw .= ' ' . ($withNot ? 'NOT ' : '') . '(' . $selectStatement->output() . ')';
        return $this;
    }

    /**
     * @param Expr[] $exprList
     * @param bool $withNot
     * @return Predicate
     */
    public function inExprList(array $exprList, bool $withNot = false)
    {
        $x = ' ';
        $x .= $withNot ? 'NOT ' : '';
        $x .= "(";
        for ($i = 0; $i < count($exprList); $i++) {
            if ($i > 0) $x .= ",";
            $x .= $exprList[$i]->output();
        }
        $x .= ")";
        $this->raw .= $x;
        return $this;
    }

    public function between(BitExpr $left, Predicate $right, bool $withNot = false)
    {
        $x = ' ';
        $x .= $withNot ? 'NOT ' : '';
        $x .= "BETWEEN " . $left->output() . ' AND ' . $right->output();
        $this->raw .= $x;
        return $this;
    }

    public function output(): string
    {
        return $this->raw;
    }

    public function soundsLike(BitExpr $bitExpr)
    {
        $this->raw .= " SOUNDS LIKE " . $bitExpr->output();
        return $this;
    }

    public function like(SimpleExpr $simpleExpr, bool $withNot = false, SimpleExpr $escape = null)
    {
        $x = ' ';
        $x .= $withNot ? 'NOT ' : '';
        $x .= "LIKE " . $simpleExpr->output();
        if ($escape !== null) $x .= ' ESCAPE ' . $escape->output();
        $this->raw .= $x;
        return $this;
    }

    public function regexp(BitExpr $bitExpr, bool $withNot = false)
    {
        $x = ' ';
        $x .= $withNot ? 'NOT ' : '';
        $x .= "REGEXP " . $bitExpr->output();
        $this->raw .= $x;
        return $this;
    }
}