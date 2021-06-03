<?php


namespace sinri\ark\database\model\implement;


use sinri\ark\database\model\ArkSQLFunction;

/**
 * Class ArkSQLAggregateFunction
 * @package sinri\ark\database\model\implement
 * @since 2.0.24 Experimental
 */
class ArkSQLAggregateFunction extends ArkSQLFunction
{
    // https://dev.mysql.com/doc/refman/8.0/en/aggregate-functions.html

    public static function makeAvg($expr, $withDistinct = false)
    {
        // AVG([DISTINCT] expr) [over_clause]
        return new static('AVG', [($withDistinct ? 'DISTINCT ' : '') . $expr]);
    }

    // BIT_AND(expr) [over_clause]
    // BIT_OR(expr) [over_clause]
    // BIT_XOR(expr) [over_clause]

    public static function makeCount($expr, $withDistinct = false)
    {
        // COUNT(expr) [over_clause]
        return new static('COUNT', [($withDistinct ? 'DISTINCT ' : '') . $expr]);
    }

    public static function makeGroupConcat($expr, $withDistinct = false, $orderByExpression = '', $separator = ',')
    {
        // GROUP_CONCAT([DISTINCT] expr [,expr ...]
        //             [ORDER BY {unsigned_integer | col_name | expr}
        //                 [ASC | DESC] [,col_name ...]]
        //             [SEPARATOR str_val])
        $p = ($withDistinct ? 'DISTINCT ' : '') . $expr;
        if (strlen(trim($orderByExpression)) > 0) {
            $p .= ' ORDER BY ' . $orderByExpression;
        }
        if ($separator !== ',' && strlen(trim($orderByExpression)) > 0) {
            $p .= ' SEPARATOR ' . $separator;
        }
        return new static('GROUP_CONCAT', [$p]);
    }

    // JSON_ARRAYAGG(col_or_expr) [over_clause]
    // JSON_OBJECTAGG(key, value) [over_clause]

    public static function makeMax($expr, $withDistinct = false)
    {
        // MAX([DISTINCT] expr) [over_clause]
        return new static('MAX', [($withDistinct ? 'DISTINCT ' : '') . $expr]);
    }

    public static function makeMin($expr, $withDistinct = false)
    {
        // MIN([DISTINCT] expr) [over_clause]
        return new static('MIN', [($withDistinct ? 'DISTINCT ' : '') . $expr]);
    }

    // STD(expr) [over_clause]
    // STDDEV(expr) [over_clause]
    // STDDEV_POP(expr) [over_clause]
    // STDDEV_SAMP(expr) [over_clause]

    public static function makeSum($expr, $withDistinct = false)
    {
        // SUM([DISTINCT] expr) [over_clause]
        return new static('SUM', [($withDistinct ? 'DISTINCT ' : '') . $expr]);
    }

    // VAR_POP(expr) [over_clause]
    // VAR_SAMP(expr) [over_clause]
    // VARIANCE(expr) [over_clause]
}