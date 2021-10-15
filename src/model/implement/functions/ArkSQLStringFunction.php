<?php


namespace sinri\ark\database\model\implement\functions;


use sinri\ark\database\exception\ArkPDOSQLBuilderError;
use sinri\ark\database\model\ArkSQLFunction;
use sinri\ark\database\pdo\ArkPDO;

/**
 * Class ArkSQLStringFunction
 * @package sinri\ark\database\model\implement
 * @since 2.0.24 Experimental
 * @since 2.1 reconstructed
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
     * @param string $quoteType
     * @return static
     */
    public static function makeAscii($str, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('ASCII'))->appendParameter($str, $quoteType);
    }

    /**
     * Returns a string representation of the binary value of N,
     * where N is a longlong (BIGINT) number. This is equivalent to CONV(N,10,2).
     * Returns NULL if N is NULL.
     *
     * @param $n
     * @param string $quoteType
     * @return static
     */
    public static function makeBin($n, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('BIN'))->appendParameter($n, $quoteType);
    }

    /**
     * Returns the length of the string str in bits.
     *
     * @param $str
     * @param string $quoteType
     * @return static
     */
    public static function makeBitLength($str, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('BIT_LENGTH'))->appendParameter($str, $quoteType);
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
     * @param string $quoteType
     * @return static
     */
    public static function makeChar($chars, $usingCharsetName = null, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        if (!is_array($chars)) {
            $chars = [$chars];
        }
        if (count($chars) === 0) {
            throw new ArkPDOSQLBuilderError('Function Char Parameter Error');
        }
        $x = new static('CHAR');
        foreach ($chars as $char) {
            $x->appendParameter($char, $quoteType);
        }
        if ($usingCharsetName !== null) {
            $x->setTailText('USING ' . $usingCharsetName);
        }
        return $x;
    }

    /**
     * Returns the length of the string str, measured in characters.
     * A multibyte character counts as a single character.
     * This means that for a string containing five 2-byte characters,
     *  LENGTH() returns 10,
     *  whereas CHAR_LENGTH() returns 5.
     *
     * @param $str
     * @param string $quoteType
     * @return static
     */
    public static function makeCharLength($str, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('CHAR_LENGTH'))->appendParameter($str, $quoteType);
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
    public static function makeConcat(array $parts, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        if (count($parts) == 0) {
            throw new ArkPDOSQLBuilderError('CONCAT NEED ONE OR MORE ARGUMENTS');
        }
        $x = (new static('CONCAT'));
        foreach ($parts as $part) {
            $x->appendParameter($part, $quoteType);
        }
        return $x;
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
     * @param string $quoteType
     * @return static
     */
    public static function makeConcatWithSeparator(string $separator, array $parts, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        if (count($parts) == 0) {
            throw new ArkPDOSQLBuilderError('CONCAT NEED ONE OR MORE ARGUMENTS');
        }
        $x = (new static('CONCAT_WS'));
        $x->appendParameter($separator, ArkPDO::QUOTE_TYPE_STRING);
        foreach ($parts as $part) {
            $x->appendParameter($part, $quoteType);
        }
        return $x;
    }

    // ELT(N,str1,str2,str3,...)
    // EXPORT_SET(bits,on,off[,separator[,number_of_bits]])
    // FIELD(str,str1,str2,str3,...)
    // FIND_IN_SET(str,strlist)
    // FORMAT(X,D[,locale])

    public static function makeFromBase64($str, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('FROM_BASE64'))->appendParameter($str, $quoteType);
    }

    public static function makeHex($x, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('HEX'))->appendParameter($x, $quoteType);
    }

    // INSERT(str,pos,len,newstr)
    // INSTR(str,substr)
    // LCASE() is a synonym for LOWER().

    /**
     * Returns the leftmost len characters from the string str, or NULL if any argument is NULL.
     * This function is multibyte safe.
     *
     * @param $str
     * @param int $len
     * @param string $quoteType
     * @return static
     */
    public static function makeLeft($str, int $len, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('LEFT'))
            ->appendParameter($str, $quoteType)
            ->appendParameter($len, ArkPDO::QUOTE_TYPE_INT);
    }

    /**
     * Returns the length of the string str, measured in bytes.
     * A multibyte character counts as multiple bytes.
     * This means that for a string containing five 2-byte characters, LENGTH() returns 10,
     * whereas CHAR_LENGTH() returns 5.
     *
     * @param $str
     * @param string $quoteType
     * @return static
     */
    public static function makeLength($str, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('LENGTH'))
            ->appendParameter($str, $quoteType);
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
     * @param string $quoteTypeForNeedle
     * @param string $quoteTypeForHaystack
     * @return static
     */
    public static function makeLocate($substr, $str, $pos = null, $quoteTypeForNeedle = ArkPDO::QUOTE_TYPE_VALUE, $quoteTypeForHaystack = ArkPDO::QUOTE_TYPE_RAW)
    {
        $x = new static('LOCATE');
        $x->appendParameter($substr, $quoteTypeForNeedle);
        $x->appendParameter($str, $quoteTypeForHaystack);
        if ($pos !== null) {
            $x->appendParameter($pos);
        }
        return $x;
    }

    /**
     * Returns the string str with all characters changed to lowercase according to the current character set mapping.
     * The default is utf8mb4.
     *
     * @param $str
     * @param string $quoteType
     * @return static
     */
    public static function makeLower($str, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('LOWER'))->appendParameter($str, $quoteType);
    }

    // LPAD(str,len,padstr)

    /**
     * Returns the string str with leading space characters removed.
     *
     * @param $str
     * @param string $quoteType
     * @return static
     */
    public static function makeLeftTrim($str, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('LTRIM'))->appendParameter($str, $quoteType);
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
     * @param string $quoteType
     * @return static
     */
    public static function makeOct($x, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('OCT'))->appendParameter($x, $quoteType);
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
     * @param string $quoteType
     * @return static
     */
    public static function makeOrd($x, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('ORD'))->appendParameter($x, $quoteType);
    }

    // POSITION(substr IN str) is a synonym for LOCATE(substr,str).

    /**
     * Quotes a string to produce a result that can be used as a properly escaped data value in an SQL statement.
     * The string is returned enclosed by single quotation marks and with each instance of backslash (\),
     * single quote ('), ASCII NUL, and Control+Z preceded by a backslash.
     * If the argument is NULL, the return value is the word “NULL” without enclosing single quotation marks.
     *
     * @param $str
     * @param string $quoteType
     * @return static
     */
    public static function makeQuote($str, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('QUOTE'))->appendParameter($str, $quoteType);
    }

    /**
     * Returns a string consisting of the string str repeated count times.
     * If count is less than 1, returns an empty string.
     * Returns NULL if str or count are NULL.
     *
     * @param $str
     * @param int $count
     * @param string $quoteType
     * @return static
     */
    public static function makeRepeat($str, int $count, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('REPEAT'))
            ->appendParameter($str, $quoteType)
            ->appendParameter($count, ArkPDO::QUOTE_TYPE_INT);
    }

    /**
     * Returns the string str with all occurrences of the string from_str replaced by the string to_str.
     * REPLACE() performs a case-sensitive match when searching for from_str.
     *
     * @param $str
     * @param $from_str
     * @param $to_str
     * @param string $quoteType
     * @return static
     */
    public static function makeReplace($str, $from_str, $to_str, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('REPLACE'))
            ->appendParameter($str, $quoteType)
            ->appendParameter($from_str, ArkPDO::QUOTE_TYPE_STRING)
            ->appendParameter($to_str, ArkPDO::QUOTE_TYPE_STRING);
    }

    /**
     * Returns the string str with the order of the characters reversed.
     *
     * @param $str
     * @param string $quoteType
     * @return static
     */
    public static function makeReverse($str, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('REVERSE'))->appendParameter($str, $quoteType);
    }

    /**
     * Returns the rightmost len characters from the string str, or NULL if any argument is NULL.
     *
     * @param $str
     * @param int $len
     * @param string $quoteType
     * @return static
     */
    public static function makeRight($str, int $len, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('RIGHT'))
            ->appendParameter($str, $quoteType)
            ->appendParameter($len, ArkPDO::QUOTE_TYPE_INT);
    }

    // RPAD(str,len,padstr)

    /**
     * Returns the string str with trailing space characters removed.
     *
     * @param $str
     * @param string $quoteType
     * @return static
     */
    public static function makeRightTrim($str, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('RTRIM'))->appendParameter($str, $quoteType);
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
     * @param int $pos
     * @param int|null $len
     * @param string $quoteType
     * @return static
     */
    public static function makeSubString($str, int $pos, int $len = null, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        $x = new static('SUBSTRING');
        $x->appendParameter($str, $quoteType);
        $x->appendParameter($pos, ArkPDO::QUOTE_TYPE_INT);
        if ($len !== null) {
            $x->appendParameter($len, ArkPDO::QUOTE_TYPE_INT);
        }
        return $x;
    }

    // SUBSTRING_INDEX(str,delim,count)

    /**
     * @see https://dev.mysql.com/doc/refman/8.0/en/string-functions.html#function_to-base64
     *
     * @param $str
     * @param string $quoteType
     * @return static
     */
    public static function makeToBase64($str, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('TO_BASE64'))->appendParameter($str, $quoteType);
    }

    /**
     * TRIM([{BOTH | LEADING | TRAILING} [remstr] FROM] str), TRIM([remstr FROM] str)
     *
     * @param string $originalStr str
     * @param string|null $removeStr remstr
     * @param string $type BOTH | LEADING | TRAILING
     * @return static
     */
    public static function makeTrim($originalStr, $removeStr = null, $type = 'BOTH', $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        if ($removeStr === null) {
            return (new static('TRIM'))
                ->appendParameter($originalStr, $quoteType);
        } else {
            return (new static('TRIM'))
                ->setHeadText($type . ' ' . $removeStr . ' FROM')
                ->appendParameter($originalStr, $quoteType);
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
     * @param string $quoteType
     * @return static
     */
    public static function makeUnHex($x, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('UNHEX'))->appendParameter($x, $quoteType);
    }

    /**
     * Returns the string str with all characters changed to uppercase according to the current character set mapping.
     * The default is utf8mb4.
     *
     * @param $str
     * @param string $quoteType
     * @return static
     */
    public static function makeUpper($str, $quoteType = ArkPDO::QUOTE_TYPE_RAW)
    {
        return (new static('UPPER'))->appendParameter($str, $quoteType);
    }

    // WEIGHT_STRING(str [AS {CHAR|BINARY}(N)] [flags])


}