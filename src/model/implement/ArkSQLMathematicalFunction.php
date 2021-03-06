<?php


namespace sinri\ark\database\model\implement;


use sinri\ark\database\model\ArkSQLFunction;

/**
 * Class ArkSQLMathematicalFunction
 * @package sinri\ark\database\model\implement
 * @since 2.0.24 Experimental
 */
class ArkSQLMathematicalFunction extends ArkSQLFunction
{
    // https://dev.mysql.com/doc/refman/8.0/en/mathematical-functions.html

    /**
     * Returns the absolute value of X, or NULL if X is NULL.
     * @param string|numeric $x Field name or number
     * @return static
     */
    public static function makeAbs($x)
    {
        return new static('ABS', [$x]);
    }

    // ACOS(x)
    // ASIN(x)
    // ATAN(x)
    // ATAN(Y,X), ATAN2(Y,X)

    /**
     * CEIL() is a synonym for CEILING().
     * @param string|numeric $x
     * @return static
     */
    public static function makeCeil($x)
    {
        return self::makeCeiling($x);
    }

    /**
     * Returns the smallest integer value not less than X.
     * @param string|numeric $x
     * @return static
     */
    public static function makeCeiling($x)
    {
        return new static('CEILING', [$x]);
    }

    /**
     * Converts numbers between different number bases.
     * Returns a string representation of the number N, converted from base from_base to base to_base.
     * Returns NULL if any argument is NULL.
     * The argument N is interpreted as an integer, but may be specified as an integer or a string.
     * The minimum base is 2 and the maximum base is 36.
     * If from_base is a negative number, N is regarded as a signed number.
     * Otherwise, N is treated as unsigned.
     * CONV() works with 64-bit precision.
     *
     * @param string|numeric $n
     * @param int $fromBase
     * @param int $toBase
     * @return static
     */
    public static function makeConv($n, int $fromBase, int $toBase)
    {
        return new static('CONV', [$n, $fromBase, $toBase]);
    }

    // COS(X)
    // COT(X)
    // CRC32(expr)
    // DEGREES(X)
    // EXP(X)

    /**
     * Returns the largest integer value not greater than X.
     * @param string|numeric $x
     * @return static
     */
    public static function makeFloor($x)
    {
        return new static('FLOOR', [$x]);
    }

    // FORMAT(X,D)
    // HEX(N_or_S)
    // LN(X)
    // LOG(X), LOG(B,X)
    // LOG2(X)
    // LOG10(X)
    // MOD(N,M), N % M, N MOD M
    // PI()
    // POW(X,Y)
    // POWER(X,Y) -> This is a synonym for POW().
    // RADIANS(X)

    /**
     * Returns a random floating-point value v in the range 0 <= v < 1.0.
     *
     * Notice: To obtain a random integer R in the range i <= R < j, use the expression FLOOR(i + RAND() * (j − i)).
     *
     * If an integer argument N is specified, it is used as the seed value:
     * - With a constant initializer argument, the seed is initialized once when the statement is prepared,
     *      prior to execution.
     * - With a nonconstant initializer argument (such as a column name),
     *      the seed is initialized with the value for each invocation of RAND().
     *
     * One implication of this behavior is that for equal argument values, RAND(N) returns the same value each time, and thus produces a repeatable sequence of column values.
     *
     * @param string|numeric|null $seed
     * @return static
     */
    public static function makeRand($seed = null)
    {
        $p = [];
        if ($seed !== null) {
            $p = [$seed];
        }
        return new static('RAND', $p);
    }

    /**
     * Rounds the argument X to D decimal places.
     * The rounding algorithm depends on the data type of X.
     * D defaults to 0 if not specified.
     * D can be negative to cause D digits left of the decimal point of the value X to become zero.
     * The maximum absolute value for D is 30; any digits in excess of 30 (or -30) are truncated.
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/mathematical-functions.html#function_round
     *
     * @param string|numeric $x
     * @param int|null $d
     * @return static
     */
    public static function makeRound($x, $d = null)
    {
        $p = [$x];
        if ($d !== null) {
            $p[] = $d;
        }
        return new static('ROUND', $p);
    }

    /**
     * Returns the sign of the argument as -1, 0, or 1,
     * depending on whether X is negative, zero, or positive.
     *
     * @param string|numeric $x
     * @return static
     */
    public static function makeSign($x)
    {
        return new static('SIGN', [$x]);
    }

    // SIN(X)
    // SQRT(X)
    // TAN(X)

    /**
     * Returns the number X, truncated to D decimal places.
     * If D is 0, the result has no decimal point or fractional part.
     * D can be negative to cause D digits left of the decimal point of the value X to become zero.
     *
     * All numbers are rounded toward zero.
     *
     * @param string|numeric $x
     * @param int $d
     * @return static
     */
    public static function makeTruncate($x, $d)
    {
        return new static('TRUNCATE', [$x, $d]);
    }
}