<?php


namespace sinri\ark\database\model\implement;


use sinri\ark\database\exception\ArkPDOSQLBuilderError;
use sinri\ark\database\model\ArkSQLFunction;

/**
 * Class ArkSQLStringFunction
 * @package sinri\ark\database\model\implement
 * @since 2.0.24 Experimental
 */
class ArkSQLStringFunction extends ArkSQLFunction
{
    // https://dev.mysql.com/doc/refman/8.0/en/string-functions.html

    /**
     * Returns the numeric value of the leftmost character of the string str.
     * Returns 0 if str is the empty string. Returns NULL if str is NULL.
     *
     * ASCII() works for 8-bit characters.
     *
     * @param $str
     * @return static
     */
    public static function makeAscii($str)
    {
        return new static('ASCII', [$str]);
    }

    /**
     * Returns a string representation of the binary value of N,
     * where N is a longlong (BIGINT) number. This is equivalent to CONV(N,10,2).
     * Returns NULL if N is NULL.
     *
     * @param $n
     * @return static
     */
    public static function makeBin($n)
    {
        return new static('BIN', [$n]);
    }

    /**
     * Returns the length of the string str in bits.
     *
     * @param $str
     * @return static
     */
    public static function makeBitLength($str)
    {
        return new static('BIT_LENGTH', [$str]);
    }

    /**
     * CHAR() interprets each argument N as an integer
     *  and returns a string consisting of the characters given by the code values of those integers.
     * NULL values are skipped.
     *
     * CHAR() arguments larger than 255 are converted into multiple result bytes.
     *
     * @param $chars
     * @param false $usingCharsetName
     * @return static
     */
    public static function makeChar($chars, $usingCharsetName = false)
    {
        $p = [];
        if (is_array($chars)) {
            $p = array_merge($p, $chars);
        } else {
            $p[] = $chars;
        }
        if (count($p) === 0) {
            throw new ArkPDOSQLBuilderError('Function Char Parameter Error');
        }
        if ($usingCharsetName !== null) {
            $p[count($p) - 1] = $p[count($p) - 1] . ' USING ' . $usingCharsetName;
        }
        return new static('CHAR', $p);
    }

    /**
     * Returns the length of the string str, measured in characters.
     * A multibyte character counts as a single character.
     * This means that for a string containing five 2-byte characters,
     *  LENGTH() returns 10,
     *  whereas CHAR_LENGTH() returns 5.
     *
     * @param $str
     * @return static
     */
    public static function makeCharLength($str)
    {
        return new static('CHAR_LENGTH', [$str]);
    }

    // CHARACTER_LENGTH(str) is a synonym for CHAR_LENGTH().

    /**
     * Returns the string that results from concatenating the arguments.
     * May have one or more arguments.
     * If all arguments are nonbinary strings, the result is a nonbinary string.
     * If the arguments include any binary strings, the result is a binary string.
     * A numeric argument is converted to its equivalent nonbinary string form.
     *
     * CONCAT() returns NULL if any argument is NULL.
     *
     * @param string[] $parts
     * @return static
     */
    public static function makeConcat(array $parts)
    {
        if (count($parts) == 0) {
            throw new ArkPDOSQLBuilderError('CONCAT NEED ONE OR MORE ARGUMENTS');
        }
        return new static('CONCAT', $parts);
    }

    /**
     * CONCAT_WS() stands for Concatenate With Separator and is a special form of CONCAT().
     * The first argument is the separator for the rest of the arguments.
     * The separator is added between the strings to be concatenated.
     * The separator can be a string, as can the rest of the arguments.
     * If the separator is NULL, the result is NULL.
     *
     * CONCAT_WS() does not skip empty strings. However, it does skip any NULL values after the separator argument.
     *
     * @param string $separator
     * @param array $parts
     * @return static
     */
    public static function makeConcatWithSeparator(string $separator, array $parts)
    {
        if (count($parts) == 0) {
            throw new ArkPDOSQLBuilderError('CONCAT NEED ONE OR MORE ARGUMENTS');
        }
        $p = array_merge([$separator], $parts);
        return new static('CONCAT_WS', $p);
    }

    // ELT(N,str1,str2,str3,...)
    // EXPORT_SET(bits,on,off[,separator[,number_of_bits]])
    // FIELD(str,str1,str2,str3,...)
    // FIND_IN_SET(str,strlist)
    // FORMAT(X,D[,locale])

    public static function makeFromBase64($str)
    {
        return new static('FROM_BASE64', [$str]);
    }

    public static function makeHex($x)
    {
        return new static('HEX', [$x]);
    }

    // INSERT(str,pos,len,newstr)
    // INSTR(str,substr)
    // LCASE() is a synonym for LOWER().

    /**
     * Returns the leftmost len characters from the string str, or NULL if any argument is NULL.
     * This function is multibyte safe.
     *
     * @param $str
     * @param $len
     * @return static
     */
    public static function makeLeft($str, $len)
    {
        return new static('LEFT', [$str, $len]);
    }

    /**
     * Returns the length of the string str, measured in bytes.
     * A multibyte character counts as multiple bytes.
     * This means that for a string containing five 2-byte characters, LENGTH() returns 10,
     * whereas CHAR_LENGTH() returns 5.
     *
     * @param $str
     * @return static
     */
    public static function makeLength($str)
    {
        return new static('LENGTH', [$str]);
    }

    // LOAD_FILE(file_name)

    /**
     * The first syntax returns the position of the first occurrence of substring substr in string str.
     * The second syntax returns the position of the first occurrence of substring substr in string str,
     * starting at position pos. Returns 0 if substr is not in str.
     * Returns NULL if any argument is NULL.
     *
     * This function is multibyte safe, and is case-sensitive only if at least one argument is a binary string.
     *
     * @param $substr
     * @param $str
     * @param int|null $pos
     * @return static
     */
    public static function makeLocate($substr, $str, $pos = null)
    {
        $p = [$substr, $str];
        if ($pos !== null) {
            $p[] = $pos;
        }
        return new static('LOCATE', $p);
    }

    /**
     * Returns the string str with all characters changed to lowercase according to the current character set mapping.
     * The default is utf8mb4.
     *
     * @param $str
     * @return static
     */
    public static function makeLower($str)
    {
        return new static('LOWER', [$str]);
    }

    // LPAD(str,len,padstr)

    /**
     * Returns the string str with leading space characters removed.
     *
     * @param $str
     * @return static
     */
    public static function makeLeftTrim($str)
    {
        return new static('LTRIM', [$str]);
    }

    // MAKE_SET(bits,str1,str2,...)
    // MID(str,pos,len) is a synonym for SUBSTRING(str,pos,len).

    /**
     * Returns a string representation of the octal value of N,
     * where N is a longlong (BIGINT) number.
     * This is equivalent to CONV(N,10,8).
     * Returns NULL if N is NULL.
     *
     * @param $x
     * @return static
     */
    public static function makeOct($x)
    {
        return new static('OCT', [$x]);
    }

    // OCTET_LENGTH(str) is a synonym for LENGTH().

    /**
     * If the leftmost character of the string str is a multibyte character,
     * returns the code for that character,
     * calculated from the numeric values of its constituent bytes using this formula:
     *
     *   (1st byte code) + (2nd byte code * 256) + (3rd byte code * 256^2) ...
     *
     * If the leftmost character is not a multibyte character,
     * ORD() returns the same value as the ASCII() function.
     *
     * @param $x
     * @return static
     */
    public static function makeOrd($x)
    {
        return new static('ORD', [$x]);
    }

    // POSITION(substr IN str) is a synonym for LOCATE(substr,str).

    /**
     * Quotes a string to produce a result that can be used as a properly escaped data value in an SQL statement.
     * The string is returned enclosed by single quotation marks and with each instance of backslash (\),
     * single quote ('), ASCII NUL, and Control+Z preceded by a backslash.
     * If the argument is NULL, the return value is the word “NULL” without enclosing single quotation marks.
     *
     * @param $str
     * @return static
     */
    public static function makeQuote($str)
    {
        return new static('QUOTE', [$str]);
    }

    /**
     * Returns a string consisting of the string str repeated count times.
     * If count is less than 1, returns an empty string.
     * Returns NULL if str or count are NULL.
     *
     * @param $str
     * @param $count
     * @return static
     */
    public static function makeRepeat($str, $count)
    {
        return new static('REPEAT', [$str, $count]);
    }

    /**
     * Returns the string str with all occurrences of the string from_str replaced by the string to_str.
     * REPLACE() performs a case-sensitive match when searching for from_str.
     *
     * @param $str
     * @param $from_str
     * @param $to_str
     * @return static
     */
    public static function makeReplace($str, $from_str, $to_str)
    {
        return new static('REPLACE', [$str, $from_str, $to_str]);
    }

    /**
     * Returns the string str with the order of the characters reversed.
     *
     * @param $str
     * @return static
     */
    public static function makeReverse($str)
    {
        return new static('REVERSE', [$str]);
    }

    /**
     * Returns the rightmost len characters from the string str, or NULL if any argument is NULL.
     *
     * @param $str
     * @param $len
     * @return static
     */
    public static function makeRight($str, $len)
    {
        return new static('RIGHT', [$str, $len]);
    }

    // RPAD(str,len,padstr)

    /**
     * Returns the string str with trailing space characters removed.
     *
     * @param $str
     * @return static
     */
    public static function makeRightTrim($str)
    {
        return new static('RTRIM', [$str]);
    }

    // SOUNDEX(str)
    // expr1 SOUNDS LIKE expr2
    // SPACE(N)
    // SUBSTR(str,pos), SUBSTR(str FROM pos), SUBSTR(str,pos,len), SUBSTR(str FROM pos FOR len) -> SUBSTR() is a synonym for SUBSTRING().

    /**
     * The forms without a len argument return a substring from string str starting at position pos.
     * The forms with a len argument return a substring len characters long from string str,
     * starting at position pos.
     * The forms that use FROM are standard SQL syntax.
     * It is also possible to use a negative value for pos. In this case,
     * the beginning of the substring is pos characters from the end of the string, rather than the beginning.
     * A negative value may be used for pos in any of the forms of this function.
     * A value of 0 for pos returns an empty string.
     *
     * For all forms of SUBSTRING(), the position of the first character in the string from which the substring is to be extracted is reckoned as 1.
     *
     * @param $str
     * @param $pos
     * @param int|null $len
     * @return static
     */
    public static function makeSubString($str, $pos, $len = null)
    {
        $p = [$str, $pos];
        if ($len !== null) {
            $p[] = $len;
        }
        return new static('SUBSTRING', $p);
    }

    // SUBSTRING_INDEX(str,delim,count)

    /**
     * @see https://dev.mysql.com/doc/refman/8.0/en/string-functions.html#function_to-base64
     *
     * @param $str
     * @return static
     */
    public static function makeToBase64($str)
    {
        return new static('TO_BASE64', [$str]);
    }

    /**
     * TRIM([{BOTH | LEADING | TRAILING} [remstr] FROM] str), TRIM([remstr FROM] str)
     *
     * @param string $originalStr str
     * @param string|null $removeStr remstr
     * @param string $type BOTH | LEADING | TRAILING
     * @return static
     */
    public static function makeTrim($originalStr, $removeStr = null, $type = 'BOTH')
    {
        if ($removeStr === null) {
            return new static('TRIM', [$originalStr]);
        } else {
            return new static('TRIM', [$type . ' ' . $removeStr . ' FROM ' . $originalStr]);
        }
    }

    // UCASE(str) is a synonym for UPPER().

    /**
     * For a string argument str,
     * UNHEX(str) interprets each pair of characters in the argument as a hexadecimal number
     * and converts it to the byte represented by the number.
     * The return value is a binary string.
     *
     * @param $x
     * @return static
     */
    public static function makeUnHex($x)
    {
        return new static('UNHEX', [$x]);
    }

    /**
     * Returns the string str with all characters changed to uppercase according to the current character set mapping.
     * The default is utf8mb4.
     *
     * @param $str
     * @return static
     */
    public static function makeUpper($str)
    {
        return new static('UPPER', [$str]);
    }

    // WEIGHT_STRING(str [AS {CHAR|BINARY}(N)] [flags])


}