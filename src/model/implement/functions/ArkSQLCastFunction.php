<?php


namespace sinri\ark\database\model\implement\functions;


use sinri\ark\database\model\ArkSQLFunction;
use sinri\ark\database\pdo\ArkPDO;

/**
 * Class ArkSQLCastFunction
 * @package sinri\ark\database\model\implement
 * @since 2.0.24 Experimental
 */
class ArkSQLCastFunction extends ArkSQLFunction
{
    // https://dev.mysql.com/doc/refman/8.0/en/cast-functions.html

    /**
     * @param string $expr
     * @param string $type
     * @param string $quoteType
     * @return static
     */
    public static function makeCast(string $expr, string $type, string $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        // CAST(expr AS type [ARRAY])
        return (new static('CAST'))
            ->appendParameter(ArkPDO::quoteScalar($expr, $quoteType))
            ->setTailText('AS ' . $type);
    }

    /**
     * Beginning with MySQL 8.0.22,
     *  CAST() supports retrieval of a TIMESTAMP value as being in UTC,
     *  using the AT TIMEZONE operator.
     *  The only supported time zone is UTC;
     *  this can be specified as either of '+00:00' or 'UTC'.
     *  The only return type supported by this syntax is DATETIME,
     *  with an optional precision specifier in the range of 0 to 6, inclusive.
     *
     * @param string $timestamp_value field (in data time format) or date time string
     * @param string $timezone_specifier timezone_specifier: [INTERVAL] '+00:00' | 'UTC'
     * @param int $precision
     * @return static
     */
    public static function makeCastTimeToDateTime($timestamp_value, $timezone_specifier, $precision = '', $timestamp_value_quote_type = ArkPDO::QUOTE_TYPE_RAW)
    {
        // CAST(timestamp_value AT TIME ZONE timezone_specifier AS DATETIME[(precision)])
        if (strlen(trim($precision)) > 0) {
            $precision .= '(' . $precision . ')';
        }
        return (new static('CAST'))
            ->appendParameter($timestamp_value, $timestamp_value_quote_type)
            ->setTailText(
                ' AT TIME ZONE ' . $timezone_specifier . ' AS DATETIME' . $precision
            );
    }

    // CONVERT(expr USING transcoding_name), CONVERT(expr,type)

    public static function makeConvertEncoding($expr, string $transcoding_name, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('CONVERT'))
            ->appendParameter($expr, $quoteType)
            ->setTailText('USING ' . $transcoding_name);
    }

    public static function makeConvertType($expr, $type, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('CONVERT'))
            ->appendParameter($expr, $quoteType)
            ->appendParameter($type, $quoteType);
    }
}