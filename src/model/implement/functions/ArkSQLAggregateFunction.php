<?php


namespace sinri\ark\database\model\implement\functions;


use sinri\ark\database\model\ArkSQLFunction;
use sinri\ark\database\pdo\ArkPDO;

/**
 * Class ArkSQLAggregateFunction
 * @package sinri\ark\database\model\implement
 * @since 2.0.24 Experimental
 * @since 2.1 reconstructed
 */
class ArkSQLAggregateFunction extends ArkSQLFunction
{
    // https://dev.mysql.com/doc/refman/8.0/en/aggregate-functions.html

    public static function makeAvg(string $expr, bool $withDistinct = false, string $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        // AVG([DISTINCT] expr) [over_clause]
        return (new static('AVG'))
            ->setHeadText($withDistinct ? 'DISTINCT' : '')
            ->appendParameter($expr, $quoteType);
    }

    // BIT_AND(expr) [over_clause]
    // BIT_OR(expr) [over_clause]
    // BIT_XOR(expr) [over_clause]

    public static function makeCount($exprOrList, $withDistinct = false, string $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        // COUNT(expr) [over_clause]
        // COUNT(DISTINCT expr,[expr...])
        if (is_array($exprOrList)) {
            $x = (new static('COUNT'))
                ->setHeadText($withDistinct ? 'DISTINCT' : '');
            foreach ($exprOrList as $expr) {
                $x->appendParameter($expr, $quoteType);
            }
            return $x;
        } else {
            return (new static('COUNT'))
                ->setHeadText($withDistinct ? 'DISTINCT' : '')
                ->appendParameter($exprOrList, $quoteType);
        }
    }

    public static function makeGroupConcat($exprOrList, $withDistinct = false, $orderByExpression = '', $separator = ',', string $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        // GROUP_CONCAT([DISTINCT] expr [,expr ...]
        //             [ORDER BY {unsigned_integer | col_name | expr}
        //                 [ASC | DESC] [,col_name ...]]
        //             [SEPARATOR str_val])

        $x = new static('GROUP_CONCAT');
        if ($withDistinct) {
            $x->setHeadText('DISTINCT');
        }

        if (is_array($exprOrList)) {
            foreach ($exprOrList as $expr) {
                $x->appendParameter($expr, $quoteType);
            }
        } else {
            $x->appendParameter($exprOrList, $quoteType);
        }

        $tail = '';
        if (strlen(trim($orderByExpression)) > 0) {
            $tail .= ' ORDER BY ' . $orderByExpression;
        }
        if ($separator !== ',' && strlen(trim($orderByExpression)) > 0) {
            $tail .= ' SEPARATOR ' . $separator;
        }
        $x->setTailText($tail);

        return $x;
    }

    // JSON_ARRAYAGG(col_or_expr) [over_clause]
    // JSON_OBJECTAGG(key, value) [over_clause]

    public static function makeMax($expr, $withDistinct = false, string $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        // MAX([DISTINCT] expr) [over_clause]
        return (new static('MAX'))
            ->setHeadText($withDistinct ? 'DISTINCT' : '')
            ->appendParameter($expr, $quoteType);
    }

    public static function makeMin($expr, $withDistinct = false, string $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        // MIN([DISTINCT] expr) [over_clause]
        return (new static('MIN'))
            ->setHeadText($withDistinct ? 'DISTINCT' : '')
            ->appendParameter($expr, $quoteType);
    }

    // STD(expr) [over_clause]
    // STDDEV(expr) [over_clause]
    // STDDEV_POP(expr) [over_clause]
    // STDDEV_SAMP(expr) [over_clause]

    public static function makeSum($expr, $withDistinct = false, string $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        // SUM([DISTINCT] expr) [over_clause]
        return (new static('SUM'))
            ->setHeadText($withDistinct ? 'DISTINCT' : '')
            ->appendParameter($expr, $quoteType);
    }

    // VAR_POP(expr) [over_clause]
    // VAR_SAMP(expr) [over_clause]
    // VARIANCE(expr) [over_clause]
}