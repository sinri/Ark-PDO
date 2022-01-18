<?php

namespace sinri\ark\database\test\mysql\grammar\version57\expr;

use sinri\ark\database\test\mysql\grammar\SQLComponentInterface;
use sinri\ark\database\test\mysql\grammar\version57\literal\Literal;
use sinri\ark\database\test\mysql\grammar\version57\object\Identifier;
use sinri\ark\database\test\mysql\grammar\version57\object\UserDefinedVariableName;
use sinri\ark\database\test\mysql\grammar\version57\statement\SelectStatement;

class SimpleExpr implements SQLComponentInterface
{
    /**
     * @var string
     */
    protected $raw;

    protected function __construct(string $raw)
    {
        $this->raw = $raw;
    }

    public static function makeFromLiteral(Literal $literal)
    {
        return new self($literal->output());
    }

    public static function makeFromIdentifier(Identifier $identifier)
    {
        return new self($identifier->output());
    }

    public static function makeFromFunctionCall()
    {
        // TODO function call
    }

    public static function makeFromSimpleExprAndCollate(SimpleExpr $simpleExpr, string $collate)
    {
        return new self($simpleExpr->output() . ' COLLATE ' . $collate);
    }

    public function output(): string
    {
        return $this->raw;
    }

    public static function makeFromParamMarker()
    {
        return new self("?");
    }

    public static function makeFromVariable(UserDefinedVariableName $variableName)
    {
        return new self($variableName->output());
    }

    public static function makeFromSimpleExprOrSimpleExpr(SimpleExpr $simpleExpr, SimpleExpr $another)
    {
        return new self($simpleExpr->output() . ' || ' . $another->output());
    }

    /**
     * @param string $prefix + - ! ~ BINARY
     * @param SimpleExpr $simpleExpr
     * @return SimpleExpr
     */
    public static function makeFromPrefixedSimpleExpr(string $prefix, SimpleExpr $simpleExpr)
    {
        return new self($prefix . ' ' . $simpleExpr->output());
    }

    /**
     * @param Expr[] $exprList
     */
    public static function makeFromExprList(array $exprList)
    {
        $x = [];
        foreach ($exprList as $expr) {
            $x[] = $expr->output();
        }
        return new self('(' . implode(",", $x) . ')');
    }

    /**
     * @param Expr[] $exprList
     */
    public static function makeFromRowExprList(array $exprList)
    {
        $x = [];
        foreach ($exprList as $expr) {
            $x[] = $expr->output();
        }
        return new self('ROW (' . implode(",", $x) . ')');
    }

    public static function makeFromSubQuery(SelectStatement $selectStatement)
    {
        return new self('(' . $selectStatement->output() . ')');
    }

    // {identifier expr} -> ODBC
    // match_expr
    // case_expr

    public static function makeFromExists(SelectStatement $selectStatement)
    {
        return new self('EXISTS (' . $selectStatement->output() . ')');
    }

    public static function makeFromMatchExpr()
    {
        // TODO https://dev.mysql.com/doc/refman/5.7/en/fulltext-search.html
    }

    public static function makeFromCaseExpr()
    {
        // TODO https://dev.mysql.com/doc/refman/5.7/en/flow-control-functions.html
    }

    public static function makeFromIntervalExpr(TemporalInterval $interval)
    {
        return new self($interval->output());
    }
}