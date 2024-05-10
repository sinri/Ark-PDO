<?php


namespace sinri\ark\database\model\implement;


use sinri\ark\database\model\ArkSQLFunction;

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
     * @return static
     */
    public static function makeCast(string $expr, string $type): static
    {
        // CAST(expr AS type [ARRAY])
        return new static('CAST', [$expr . ' AS ' . $type]);
    }

    /**
     * @param $timestamp_value
     * @param $timezone_specifier
     * @param string $precision
     * @return static
     */
    public static function makeCastTimeToDateTime($timestamp_value, $timezone_specifier, string $precision = ''): static
    {
        // CAST(timestamp_value AT TIME ZONE timezone_specifier AS DATETIME[(precision)])
        if (strlen(trim($precision)) > 0) {
            $precision .= '(' . $precision . ')';
        }
        return new static(
            'CAST',
            [$timestamp_value . ' AT TIME ZONE ' . $timezone_specifier . ' AS DATETIME' . $precision]
        );
    }

    // CONVERT(expr USING transcoding_name), CONVERT(expr,type)

    public static function makeConvertEncoding($expr, $transcoding_name): static
    {
        return new static('CONVERT', [$expr . ' USING ' . $transcoding_name]);
    }

    public static function makeConvertType($expr, $type): static
    {
        return new static('CONVERT', [$expr, $type]);
    }
}