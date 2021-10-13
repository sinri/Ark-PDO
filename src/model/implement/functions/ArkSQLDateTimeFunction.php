<?php


namespace sinri\ark\database\model\implement\functions;


use sinri\ark\database\model\ArkSQLFunction;
use sinri\ark\database\pdo\ArkPDO;

/**
 * Class ArkSQLDateTimeFunction
 * @package sinri\ark\database\model\implement
 * @since 2.0.24 Experimental
 */
class ArkSQLDateTimeFunction extends ArkSQLFunction
{
// https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html

    /**
     * unit Value |    Expected expr Format
     * MICROSECOND    MICROSECONDS
     * SECOND    SECONDS
     * MINUTE    MINUTES
     * HOUR    HOURS
     * DAY    DAYS
     * WEEK    WEEKS
     * MONTH    MONTHS
     * QUARTER    QUARTERS
     * YEAR    YEARS
     * SECOND_MICROSECOND    'SECONDS.MICROSECONDS'
     * MINUTE_MICROSECOND    'MINUTES:SECONDS.MICROSECONDS'
     * MINUTE_SECOND    'MINUTES:SECONDS'
     * HOUR_MICROSECOND    'HOURS:MINUTES:SECONDS.MICROSECONDS'
     * HOUR_SECOND    'HOURS:MINUTES:SECONDS'
     * HOUR_MINUTE    'HOURS:MINUTES'
     * DAY_MICROSECOND    'DAYS HOURS:MINUTES:SECONDS.MICROSECONDS'
     * DAY_SECOND    'DAYS HOURS:MINUTES:SECONDS'
     * DAY_MINUTE    'DAYS HOURS:MINUTES'
     * DAY_HOUR    'DAYS HOURS'
     * YEAR_MONTH    'YEARS-MONTHS'
     */

    // ADDDATE(date,INTERVAL expr unit), ADDDATE(expr,days)

    /**
     * SELECT ADDDATE('2008-01-02', 31); -> '2008-02-02'
     * @param $expr
     * @param int $days
     * @param string $quoteType
     * @return static
     */
    public static function makeAddDate($expr, int $days, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('ADDDATE'))->appendParameter($expr, $quoteType)->appendParameter($days);
    }


    /**
     * ADDTIME() adds expr2 to expr1 and returns the result.
     * expr1 is a time or datetime expression, and expr2 is a time expression.
     *
     * mysql> SELECT ADDTIME('2007-12-31 23:59:59.999999', '1 1:1:1.000002'); -> '2008-01-02 01:01:01.000001'
     * mysql> SELECT ADDTIME('01:00:00.999999', '02:00:00.999998'); -> '03:00:01.999997'
     *
     * @param $expr1
     * @param $expr2
     * @param string $quoteType1
     * @param string $quoteType2
     * @return static
     */
    public static function makeAddTime($expr1, $expr2, $quoteType1 = ArkPDO::QUOTE_TYPE_RAW, $quoteType2 = ArkPDO::QUOTE_TYPE_VALUE)
    {
        return (new static('ADDTIME'))
            ->appendParameter($expr1, $quoteType1)
            ->appendParameter($expr2, $quoteType2);
    }

    // CONVERT_TZ(dt,from_tz,to_tz)

    /**
     * Returns the current date as a value in 'YYYY-MM-DD' or YYYYMMDD format,
     * depending on whether the function is used in string or numeric context.
     *
     * mysql> SELECT CURDATE(); -> '2008-06-13'
     * mysql> SELECT CURDATE() + 0; -> 20080613
     *
     * @return static
     */
    public static function makeCurDate()
    {
        return new static('CURDATE');
    }

    // CURRENT_DATE and CURRENT_DATE() are synonyms for CURDATE().
    // CURRENT_TIME and CURRENT_TIME([fsp]) are synonyms for CURTIME().
    // CURRENT_TIMESTAMP and CURRENT_TIMESTAMP([fsp]) are synonyms for NOW().

    /**
     * Returns the current time as a value in 'hh:mm:ss' or hhmmss format,
     * depending on whether the function is used in string or numeric context.
     * The value is expressed in the session time zone.
     *
     * If the fsp argument is given to specify a fractional seconds precision from 0 to 6,
     * the return value includes a fractional seconds part of that many digits.
     *
     * mysql> SELECT CURTIME(); -> '23:50:26'
     * mysql> SELECT CURTIME() + 0; -> 235026.000000
     *
     * @param int|null $fsp
     * @return static
     */
    public static function makeCurTime($fsp = null)
    {
        $x = (new static('CURTIME'));
        if ($fsp !== null) {
            $x->appendParameter(intval($x));
        }
        return $x;
    }

    /**
     * Extracts the date part of the date or datetime expression expr.
     *
     * @param string $expr
     * @return static
     */
    public static function makeDate($expr, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('DATE'))->appendParameter($expr, $quoteType);
    }

    /**
     * DATEDIFF() returns expr1 − expr2 expressed as a value in days from one date to the other.
     * expr1 and expr2 are date or date-and-time expressions.
     * Only the date parts of the values are used in the calculation.
     *
     * mysql> SELECT DATEDIFF('2007-12-31 23:59:59','2007-12-30'); -> 1
     * mysql> SELECT DATEDIFF('2010-11-30 23:59:59','2010-12-31'); -> -31
     *
     * @param $expr1
     * @param $expr2
     * @param string $quoteType1
     * @param string $quoteType2
     * @return static
     */
    public static function makeDateDiff($expr1, $expr2, $quoteType1 = ArkPDO::QUOTE_TYPE_RAW, $quoteType2 = ArkPDO::QUOTE_TYPE_VALUE)
    {
        return (new static('DATEDIFF'))
            ->appendParameter($expr1, $quoteType1)
            ->appendParameter($expr2, $quoteType2);
    }

    /**
     * SELECT DATE_ADD('2008-01-02', INTERVAL 31 DAY); -> '2008-02-02'
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html#function_date-add
     *
     * @param $date
     * @param string|int $diff
     * @param string $unit
     * @param string $quoteType
     * @return static
     */
    public static function makeDateAdd($date, $diff, string $unit, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('DATE_ADD'))
            ->appendParameter($date, $quoteType)
            ->appendParameter('INTERVAL ' . $diff . ' ' . $unit);
    }

    /**
     * @see https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html#function_date-add
     *
     * @param $date
     * @param int|string $diff
     * @param string $unit
     * @param string $quoteType
     * @return static
     */
    public static function makeDateSub($date, $diff, string $unit, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('DATE_SUB'))
            ->appendParameter($date, $quoteType)
            ->appendParameter('INTERVAL ' . $diff . ' ' . $unit);
    }

    /**
     * @see https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html#function_date-format
     *
     * @param string $date
     * @param string $format
     * @param string $quoteType
     * @return static
     */
    public static function makeDateFormat(string $date, string $format, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('DATE_FORMAT'))
            ->appendParameter($date, $quoteType)
            ->appendParameter($format, ArkPDO::QUOTE_TYPE_VALUE);
    }

    /**
     * Returns the day of the month for date, in the range 1 to 31,
     * or 0 for dates such as '0000-00-00' or '2008-00-00' that have a zero day part.
     *
     * @param string $date
     * @return static
     */
    public static function makeDayOfMonth($date, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('DAYOFMONTH'))->appendParameter($date, $quoteType);
    }

    // DAY() is a synonym for DAYOFMONTH().
    // DAYNAME(date)

    /**
     * Returns the weekday index for date (1 = Sunday, 2 = Monday, …, 7 = Saturday).
     * These index values correspond to the ODBC standard.
     *
     * @param $date
     * @param string $quoteType
     * @return static
     */
    public static function makeDayOfWeek($date, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('DAYOFWEEK'))->appendParameter($date, $quoteType);
    }

    /**
     * Returns the day of the year for date, in the range 1 to 366.
     *
     * @param $date
     * @param string $quoteType
     * @return static
     */
    public static function makeDayOfYear($date, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('DAYOFYEAR'))->appendParameter($date, $quoteType);
    }

    /**
     * The EXTRACT() function uses the same kinds of unit specifiers as DATE_ADD() or DATE_SUB(),
     * but extracts parts from the date rather than performing date arithmetic.
     * For information on the unit argument, see Temporal Intervals.
     *
     * @param string $unit
     * @param $date
     * @param string $quoteType
     * @return static
     */
    public static function makeExtract(string $unit, $date, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('EXTRACT'))
            ->setHeadText($unit . ' FROM')
            ->appendParameter($date, $quoteType);
    }

    // FROM_DAYS(N)

    /**
     * Returns a representation of the unix_timestamp argument as a value in 'YYYY-MM-DD hh:mm:ss'
     * or YYYYMMDDhhmmss format, depending on whether the function is used in a string or numeric context.
     * unix_timestamp is an internal timestamp value representing seconds since '1970-01-01 00:00:00' UTC,
     * such as produced by the UNIX_TIMESTAMP() function.
     *
     * The return value is expressed in the session time zone.
     * (Clients can set the session time zone as described in Section 5.1.15, “MySQL Server Time Zone Support”.)
     * The format string, if given,
     * is used to format the result the same way as described in the entry for the DATE_FORMAT() function.
     *
     * @param $unixTimestamp
     * @param string|null $format
     * @param string $quoteType
     * @return static
     */
    public static function makeFromUnixTime($unixTimestamp, $format = null, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        $x = new static('FROM_UNIXTIME');
        $x->appendParameter($unixTimestamp, $quoteType);
        if ($format !== null) {
            $x->appendParameter($format, ArkPDO::QUOTE_TYPE_VALUE);
        }
        return $x;
    }

    // GET_FORMAT({DATE|TIME|DATETIME}, {'EUR'|'USA'|'JIS'|'ISO'|'INTERNAL'})

    /**
     * Returns the hour for time.
     * The range of the return value is 0 to 23 for time-of-day values.
     * However, the range of TIME values actually is much larger, so HOUR can return values greater than 23.
     *
     * @param $time
     * @param string $quoteType
     * @return static
     */
    public static function makeHour($time, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('HOUR'))->appendParameter($time, $quoteType);
    }

    // LAST_DAY(date)
    // LOCALTIME and LOCALTIME([fsp]) are synonyms for NOW().
    // LOCALTIMESTAMP and LOCALTIMESTAMP([fsp]) are synonyms for NOW().
    // MAKEDATE(year,dayofyear)
    // MAKETIME(hour,minute,second)

    /**
     * Returns the microseconds from the time or datetime expression expr as a number in the range from 0 to 999999.
     *
     * @param $expr
     * @param string $quoteType
     * @return static
     */
    public static function makeMicroSecond($expr, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('MICROSECOND'))->appendParameter($expr, $quoteType);
    }

    /**
     * Returns the minute for time, in the range 0 to 59.
     * @param $time
     * @param string $quoteType
     * @return static
     */
    public static function makeMinute($time, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('MINUTE'))->appendParameter($time, $quoteType);
    }

    public static function makeMonth($date, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('MONTH'))->appendParameter($date, $quoteType);
    }

    // MONTHNAME(date)

    /**
     * Returns the current date and time as a value in 'YYYY-MM-DD hh:mm:ss' or YYYYMMDDhhmmss format,
     * depending on whether the function is used in string or numeric context.
     * The value is expressed in the session time zone.
     *
     * If the fsp argument is given to specify a fractional seconds precision from 0 to 6,
     * the return value includes a fractional seconds part of that many digits.
     *
     * @param int|null $fsp 0-6
     * @return static
     */
    public static function makeNow($fsp = null)
    {
        $x = new static('NOW');
        if ($fsp !== null) {
            $x->appendParameter(intval($fsp));
        }
        return $x;
    }

    // PERIOD_ADD(P,N)
    // PERIOD_DIFF(P1,P2)
    // QUARTER(date)

    public static function makeQuarter($date, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('QUARTER'))->appendParameter($date, $quoteType);
    }

    public static function makeSecond($time, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('SECOND'))->appendParameter($time, $quoteType);
    }

    // SEC_TO_TIME(seconds)
    // STR_TO_DATE(str,format)

    public static function makeSubDate($expr, $days, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('SUBDATE'))
            ->appendParameter($expr, $quoteType)
            ->appendParameter($days);
    }

    public static function makeSubTime($expr1, $expr2, $quoteType1 = ArkPDO::QUOTE_TYPE_RAW, $quoteType2 = ArkPDO::QUOTE_TYPE_VALUE)
    {
        return (new static('SUBTIME'))
            ->appendParameter($expr1, $quoteType1)
            ->appendParameter($expr2, $quoteType2);
    }

    /**
     * Returns the current date and time as a value in 'YYYY-MM-DD hh:mm:ss' or YYYYMMDDhhmmss format,
     * depending on whether the function is used in string or numeric context.
     *
     * If the fsp argument is given to specify a fractional seconds precision from 0 to 6,
     * the return value includes a fractional seconds part of that many digits.
     *
     * NOTICE:
     * SYSDATE() returns the time at which it executes.
     * This differs from the behavior for NOW(),
     * which returns a constant time that indicates the time at which the statement began to execute.
     * (Within a stored function or trigger,
     * NOW() returns the time at which the function or triggering statement began to execute.)
     *
     * @param int|null $fsp 0-6
     * @return static
     */
    public static function makeSysDate($fsp = null)
    {
        $x = new static('SYSDATE');
        if ($fsp !== null) {
            $x->appendParameter(intval($fsp));
        }
        return $x;
    }

    public static function makeTime($expr, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('TIME'))->appendParameter($expr, $quoteType);
    }

    // TIMEDIFF(expr1,expr2)

    /**
     * With a single argument,
     *  this function returns the date or datetime expression expr as a datetime value.
     *
     * mysql> SELECT TIMESTAMP('2003-12-31'); -> '2003-12-31 00:00:00'
     *
     * With two arguments,
     *  it adds the time expression expr2 to the date or datetime expression expr1
     *  and returns the result as a datetime value.
     *
     * mysql> SELECT TIMESTAMP('2003-12-31 12:00:00','12:00:00'); -> '2004-01-01 00:00:00'
     *
     * @param string $expr1
     * @param string|null $expr2
     * @return static
     */
    public static function makeTimestamp($expr1, $expr2 = null, $quoteType1 = ArkPDO::QUOTE_TYPE_RAW, $quoteType2 = ArkPDO::QUOTE_TYPE_RAW)
    {
        $x = new static('TIMESTAMP');
        $x->appendParameter($expr1, $quoteType1);
        if ($expr2 !== null) {
            $x->appendParameter($expr2, $quoteType2);
        }
        return $x;
    }

    // TIMESTAMPADD(unit,interval,datetime_expr)
    // TIMESTAMPDIFF(unit,datetime_expr1,datetime_expr2)
    // TIME_FORMAT(time,format)
    // TIME_TO_SEC(time)
    // TO_DAYS(date)
    // TO_SECONDS(expr)

    /**
     * If UNIX_TIMESTAMP() is called with no date argument,
     *  it returns a Unix timestamp representing seconds since '1970-01-01 00:00:00' UTC.
     *
     * If UNIX_TIMESTAMP() is called with a date argument,
     *  it returns the value of the argument as seconds since '1970-01-01 00:00:00' UTC.
     *  The server interprets date as a value in the session time zone
     *  and converts it to an internal Unix timestamp value in UTC.
     *  (Clients can set the session time zone as described in Section 5.1.15, “MySQL Server Time Zone Support”.)
     *  The date argument may be a DATE, DATETIME, or TIMESTAMP string,
     *  or a number in YYMMDD, YYMMDDhhmmss, YYYYMMDD, or YYYYMMDDhhmmss format.
     *  If the argument includes a time part, it may optionally include a fractional seconds part.
     *
     * The return value is an integer if no argument is given
     *  or the argument does not include a fractional seconds part,
     *  or DECIMAL if an argument is given that includes a fractional seconds part.
     *
     * When the date argument is a TIMESTAMP column,
     *  UNIX_TIMESTAMP() returns the internal timestamp value directly,
     *  with no implicit “string-to-Unix-timestamp” conversion.
     *
     * The valid range of argument values is the same as for the TIMESTAMP data type:
     *  '1970-01-01 00:00:01.000000' UTC to '2038-01-19 03:14:07.999999' UTC.
     *  If you pass an out-of-range date to UNIX_TIMESTAMP(), it returns 0.
     *
     * mysql> SELECT UNIX_TIMESTAMP(); -> 1447431666
     * mysql> SELECT UNIX_TIMESTAMP('2015-11-13 10:20:19'); -> 1447431619
     * mysql> SELECT UNIX_TIMESTAMP('2015-11-13 10:20:19.012'); -> 1447431619.012
     *
     * @param string|null $date
     * @return static
     */
    public static function makeUnixTimestamp($date = null, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        $x = new static('UNIX_TIMESTAMP');
        if ($date !== null) {
            $x->appendParameter($date, $quoteType);
        }
        return $x;
    }

    // UTC_DATE, UTC_DATE()
    // UTC_TIME, UTC_TIME([fsp])
    // UTC_TIMESTAMP, UTC_TIMESTAMP([fsp])
    // WEEK(date[,mode])

    /**
     * Returns the weekday index for date (0 = Monday, 1 = Tuesday, … 6 = Sunday).
     *
     * @param $date
     * @param string $quoteType
     * @return static
     */
    public static function makeWeekday($date, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('WEEKDAY'))->appendParameter($date, $quoteType);
    }

    /**
     * Returns the calendar week of the date as a number in the range from 1 to 53.
     * WEEKOFYEAR() is a compatibility function that is equivalent to WEEK(date,3).
     *
     * @param $date
     * @param string $quoteType
     * @return static
     */
    public static function makeWeekOfYear($date, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('WEEKOFYEAR'))->appendParameter($date, $quoteType);
    }

    /**
     * Returns the year for date, in the range 1000 to 9999, or 0 for the “zero” date.
     *
     * @param $date
     * @param string $quoteType
     * @return static
     */
    public static function makeYear($date, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('YEAR'))->appendParameter($date, $quoteType);
    }

    // YEARWEEK(date), YEARWEEK(date,mode)
}