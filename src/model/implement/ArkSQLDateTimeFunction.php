<?php


namespace sinri\ark\database\model\implement;


use sinri\ark\database\model\ArkSQLFunction;

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
     * @param $days
     * @return static
     */
    public static function makeAddDate($expr, $days)
    {
        return new static('ADDDATE', [$expr, $days]);
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
     * @return static
     */
    public static function makeAddTime($expr1, $expr2)
    {
        return new static('ADDTIME', [$expr1, $expr2]);
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
        return new static('CURDATE', []);
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
        $p = [];
        if ($fsp !== null) {
            $p = [$fsp];
        }
        return new static('CURTIME', $p);
    }

    /**
     * Extracts the date part of the date or datetime expression expr.
     *
     * @param string $expr
     * @return static
     */
    public static function makeDate($expr)
    {
        return new static('DATE', [$expr]);
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
     * @return static
     */
    public static function makeDateDiff($expr1, $expr2)
    {
        return new static('DATEDIFF', [$expr1, $expr2]);
    }

    /**
     * SELECT DATE_ADD('2008-01-02', INTERVAL 31 DAY); -> '2008-02-02'
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html#function_date-add
     *
     * @param $date
     * @param $expr
     * @param $unit
     * @return static
     */
    public static function makeDateAdd($date, $expr, $unit)
    {
        return new static('DATE_ADD', [$date, 'INTERVAL ' . $expr . ' ' . $unit]);
    }

    /**
     * @see https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html#function_date-add
     *
     * @param $date
     * @param $expr
     * @param $unit
     * @return static
     */
    public static function makeDateSub($date, $expr, $unit)
    {
        return new static('DATE_SUB', [$date, 'INTERVAL ' . $expr . ' ' . $unit]);
    }

    /**
     * @see https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html#function_date-format
     *
     * @param string $date
     * @param string $format
     * @return static
     */
    public static function makeDateFormat(string $date, string $format)
    {
        return new static('DATE_FORMAT', [$date, $format]);
    }

    /**
     * Returns the day of the month for date, in the range 1 to 31,
     * or 0 for dates such as '0000-00-00' or '2008-00-00' that have a zero day part.
     *
     * @param string $date
     * @return static
     */
    public static function makeDayOfMonth($date)
    {
        return new static('DAYOFMONTH', [$date]);
    }

    // DAY() is a synonym for DAYOFMONTH().
    // DAYNAME(date)

    /**
     * Returns the weekday index for date (1 = Sunday, 2 = Monday, …, 7 = Saturday).
     * These index values correspond to the ODBC standard.
     *
     * @param $date
     * @return static
     */
    public static function makeDayOfWeek($date)
    {
        return new static('DAYOFWEEK', [$date]);
    }

    /**
     * Returns the day of the year for date, in the range 1 to 366.
     *
     * @param $date
     * @return static
     */
    public static function makeDayOfYear($date)
    {
        return new static('DAYOFYEAR', [$date]);
    }

    /**
     * The EXTRACT() function uses the same kinds of unit specifiers as DATE_ADD() or DATE_SUB(),
     * but extracts parts from the date rather than performing date arithmetic.
     * For information on the unit argument, see Temporal Intervals.
     *
     * @param $unit
     * @param $date
     * @return static
     */
    public static function makeExtract($unit, $date)
    {
        return new static('EXTRACT', [$unit . ' FROM ' . $date]);
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
     * @return static
     */
    public static function makeFromUnixTime($unixTimestamp, $format = null)
    {
        $p = [$unixTimestamp];
        if ($format !== null) {
            $p[] = $format;
        }
        return new static('FROM_UNIXTIME', $p);
    }

    // GET_FORMAT({DATE|TIME|DATETIME}, {'EUR'|'USA'|'JIS'|'ISO'|'INTERNAL'})

    /**
     * Returns the hour for time.
     * The range of the return value is 0 to 23 for time-of-day values.
     * However, the range of TIME values actually is much larger, so HOUR can return values greater than 23.
     *
     * @param $time
     * @return static
     */
    public static function makeHour($time)
    {
        return new static('HOUR', [$time]);
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
     * @return static
     */
    public static function makeMicroSecond($expr)
    {
        return new static('MICROSECOND', [$expr]);
    }

    /**
     * Returns the minute for time, in the range 0 to 59.
     * @param $time
     * @return static
     */
    public static function makeMinute($time)
    {
        return new static('MINUTE', [$time]);
    }

    public static function makeMonth($date)
    {
        return new static('MONTH', [$date]);
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
        $p = [];
        if ($fsp !== null) {
            $p[] = $fsp;
        }
        return new static('NOW', $p);
    }

    // PERIOD_ADD(P,N)
    // PERIOD_DIFF(P1,P2)
    // QUARTER(date)

    public static function makeQuarter($date)
    {
        return new static('QUARTER', [$date]);
    }

    public static function makeSecond($time)
    {
        return new static('SECOND', [$time]);
    }

    // SEC_TO_TIME(seconds)
    // STR_TO_DATE(str,format)

    public static function makeSubDate($expr, $days)
    {
        return new static('SUBDATE', [$expr, $days]);
    }

    public static function makeSubTime($expr1, $expr2)
    {
        return new static('SUBTIME', [$expr1, $expr2]);
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
        $p = [];
        if ($fsp !== null) {
            $p[] = $fsp;
        }
        return new static('SYSDATE', $p);
    }

    public static function makeTime($expr)
    {
        return new static('TIME', [$expr]);
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
    public static function makeTimestamp($expr1, $expr2 = null)
    {
        $p = [$expr1];
        if ($expr2 !== null) {
            $p[] = $expr2;
        }
        return new static('TIMESTAMP', $p);
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
    public static function makeUnixTimestamp($date = null)
    {
        $p = [];
        if ($date !== null) {
            $p[] = $date;
        }
        return new static('UNIX_TIMESTAMP', $p);
    }

    // UTC_DATE, UTC_DATE()
    // UTC_TIME, UTC_TIME([fsp])
    // UTC_TIMESTAMP, UTC_TIMESTAMP([fsp])
    // WEEK(date[,mode])

    /**
     * Returns the weekday index for date (0 = Monday, 1 = Tuesday, … 6 = Sunday).
     *
     * @param $date
     * @return static
     */
    public static function makeWeekday($date)
    {
        return new static('WEEKDAY', [$date]);
    }

    /**
     * Returns the calendar week of the date as a number in the range from 1 to 53.
     * WEEKOFYEAR() is a compatibility function that is equivalent to WEEK(date,3).
     *
     * @param $date
     * @return static
     */
    public static function makeWeekOfYear($date)
    {
        return new static('WEEKOFYEAR', [$date]);
    }

    /**
     * Returns the year for date, in the range 1000 to 9999, or 0 for the “zero” date.
     *
     * @param $date
     * @return static
     */
    public static function makeYear($date)
    {
        return new static('YEAR', [$date]);
    }

    // YEARWEEK(date), YEARWEEK(date,mode)
}