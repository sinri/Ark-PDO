<?php


namespace sinri\ark\database\model\implement;


use sinri\ark\database\model\ArkSQLFunction;

/**
 * Class ArkSQLFlowControlFunction
 * @package sinri\ark\database\model\implement
 * @since 2.0.24 Experimental
 */
class ArkSQLFlowControlFunction extends ArkSQLFunction
{
// https://dev.mysql.com/doc/refman/8.0/en/flow-control-functions.html

    // CASE is treated as CONDITION

    /**
     * If expr1 is TRUE (expr1 <> 0 and expr1 <> NULL), IF() returns expr2. Otherwise, it returns expr3.
     *
     * @param scalar $boolConditionExpression expr1
     * @param scalar $expressionForTrue expr2
     * @param scalar $expressionForFalse expr3
     * @return static
     */
    public static function makeIf($boolConditionExpression, $expressionForTrue, $expressionForFalse)
    {
        return new static('IF', [$boolConditionExpression, $expressionForTrue, $expressionForFalse]);
    }

    /**
     * If expr1 is not NULL, IFNULL() returns expr1; otherwise it returns expr2.
     *
     * @param scalar $originalExpression expr1
     * @param scalar $expressionForNull expr2
     * @return static
     */
    public static function makeIfNull($originalExpression, $expressionForNull)
    {
        return new static('IFNULL', [$originalExpression, $expressionForNull]);
    }

    /**
     * Returns NULL if expr1 = expr2 is true, otherwise returns expr1. This is the same as CASE WHEN expr1 = expr2 THEN NULL ELSE expr1 END.
     *
     * Note: MySQL evaluates expr1 twice if the arguments are not equal.
     * @param scalar $a expr1
     * @param scalar $b expr2
     * @return static
     */
    public static function makeNullIf($a, $b)
    {
        return new static('NULLIF', [$a, $b]);
    }
}